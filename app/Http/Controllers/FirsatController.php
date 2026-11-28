<?php

namespace App\Http\Controllers;

use App\Models\Firsat;
use App\Models\Vehicle;
use App\Models\VehicleCost;
use App\Support\MaliyetHesaplayici;
use App\Support\Para;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Açık artırma fırsatı değerlendirici: aday araç → gümrüklü maliyet + beklenen kâr
 * + 5 yaş uygunluğu + AL/GEÇ kararı. İzleme listesi; alınırsa araca çevrilir.
 */
class FirsatController extends Controller
{
    public function index(Request $request): View
    {
        $firsatlar = Firsat::query()
            ->when($request->filled('durum'), fn ($q) => $q->where('status', $request->query('durum')))
            ->when($request->filled('karar'), fn ($q) => $q->where('verdict', $request->query('karar')))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('firsat.index', compact('firsatlar'));
    }

    public function create(): View
    {
        return view('firsat.form', ['firsat' => new Firsat(['origin' => 'japonya', 'shipping_route' => 'direkt', 'usage' => 'ozel', 'buy_currency' => 'JPY'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $sonuc = $this->degerlendir($data);

        $firsat = new Firsat($data);
        $firsat->gallery_id = $request->user()->gallery_id; // admin için null olabilir → aşağıda ayarla
        $firsat->created_by = $request->user()->id;
        $firsat->fill($sonuc['kayit']);
        $firsat->status = 'izleniyor';
        if ($request->user()->isAdmin() && ! $firsat->gallery_id) {
            $firsat->gallery_id = \App\Models\Gallery::orderBy('id')->value('id');
        }
        $firsat->save();

        return redirect()->route('firsat.show', $firsat)->with('ok', 'Fırsat değerlendirildi: '.$firsat->verdictLabel());
    }

    public function show(Firsat $firsat): View
    {
        $sonuc = $this->degerlendir($firsat->only([
            'make', 'model', 'origin', 'shipping_route', 'model_year', 'first_registration', 'engine_cc',
            'fuel', 'usage', 'buy_price', 'buy_currency', 'customs_value', 'shipping', 'expected_sale',
        ]));

        return view('firsat.show', ['firsat' => $firsat, 'sonuc' => $sonuc]);
    }

    public function destroy(Firsat $firsat): RedirectResponse
    {
        $firsat->delete();

        return redirect()->route('firsat.index')->with('ok', 'Fırsat silindi.');
    }

    /** AL → fırsatı araca çevir (marka/model + maliyet kalemleriyle), tahmin akışıyla. */
    public function aracaCevir(Request $request, Firsat $firsat): RedirectResponse
    {
        $h = MaliyetHesaplayici::hesapla($this->motorGirdi($firsat->toArray()));

        // Şasi biliniyorsa doğrudan araç + kalemler oluştur; yoksa forma taşı
        if ($firsat->chassis_no) {
            $vehicle = new Vehicle([
                'gallery_id' => $firsat->gallery_id,
                'chassis_no' => $firsat->chassis_no,
                'origin' => $firsat->origin,
                'shipping_route' => $firsat->shipping_route,
                'make' => $firsat->make ?: '—',
                'model' => $firsat->model ?: '—',
                'model_year' => $firsat->model_year,
                'first_registration' => $firsat->first_registration?->toDateString(),
                'engine_cc' => $firsat->engine_cc,
                'fuel' => $this->aracYakit($firsat->fuel),
                'transmission' => 'otomatik',
                'status' => 'yolda',
                'purchase_date' => Carbon::today()->toDateString(),
                'created_by' => $request->user()->id,
            ]);
            $vehicle->save();
            foreach ($h['kostlar'] as $k) {
                $vehicle->costs()->create([
                    'category' => $k['category'], 'description' => $k['description'] ?? null,
                    'amount' => round((float) $k['amount'], 2), 'currency' => $k['currency'],
                    'rate' => (float) $k['rate'], 'base_amount' => round((float) $k['base'], 2),
                    'spent_on' => Carbon::today()->toDateString(), 'created_by' => $request->user()->id,
                ]);
            }
            $firsat->update(['status' => 'alindi', 'vehicle_id' => $vehicle->id]);

            return redirect()->route('araclar.show', $vehicle)->with('ok', 'Fırsat araca çevrildi, maliyet kalemleri eklendi.');
        }

        // Şasi yoksa: kalemleri + ön dolguyu oturuma koy, araç formuna geç (şasiyi orada girer)
        $request->session()->put('maliyet_taslak', $h['kostlar']);
        $request->session()->put('tahmin_arac', $h['arac'] + ['make' => $firsat->make, 'model' => $firsat->model, 'origin' => $firsat->origin]);
        $firsat->update(['status' => 'alindi']);

        return redirect()->route('araclar.create', ['tahmin' => 1]);
    }

    /** Maliyet motoru girdisi (firsat alanlarından) */
    private function motorGirdi(array $f): array
    {
        return [
            'alis_fiyat' => $f['buy_price'], 'alis_para' => $f['buy_currency'], 'yakit' => $f['fuel'],
            'kullanim' => $f['usage'], 'motor_cc' => $f['engine_cc'], 'model_yili' => $f['model_year'],
            'rota' => $f['shipping_route'], 'gumruk_degeri' => $f['customs_value'] ?? null, 'navlun' => $f['shipping'] ?? null,
            'kar_marji' => $f['kar_marji'] ?? null,
        ];
    }

    /** Değerlendirme: maliyet + 5 yaş + kâr + AL/GEÇ kararı */
    private function degerlendir(array $data): array
    {
        $h = MaliyetHesaplayici::hesapla($this->motorGirdi($data));
        $landed = (float) $h['toplam'];
        $suggested = (float) $h['satis'];
        $hedefMarj = (float) $h['marj'];

        // 5 yaş uygunluğu (ilk kayıt varsa ondan, yoksa model yılının ortasından)
        $ilkKayit = ! empty($data['first_registration'])
            ? Carbon::parse($data['first_registration'])
            : Carbon::create((int) $data['model_year'], 6, 15);
        $deadline = $ilkKayit->copy()->addYears(Vehicle::IMPORT_AGE_LIMIT);
        $bugun = Carbon::today();
        $importable = $deadline->gte($bugun);
        $gunKalan = (int) $bugun->diffInDays($deadline, false);

        // Kâr
        $expected = isset($data['expected_sale']) && $data['expected_sale'] !== null && $data['expected_sale'] !== ''
            ? (float) $data['expected_sale'] : null;
        $profit = $expected !== null ? round($expected - $landed, 2) : null;
        $margin = ($expected !== null && $landed > 0) ? round($profit / $landed * 100, 2) : null;

        // Karar
        if (! $importable) {
            $verdict = 'gec';
        } elseif ($margin !== null) {
            if ($margin < 0) {
                $verdict = 'gec';
            } elseif ($margin >= $hedefMarj && $gunKalan > 120) {
                $verdict = 'al';
            } else {
                $verdict = 'dusun';
            }
        } else {
            $verdict = 'dusun';
        }

        return [
            'kalemler' => $h['kalemler'],
            'landed' => $landed,
            'suggested' => $suggested,
            'expected' => $expected,
            'profit' => $profit,
            'margin' => $margin,
            'hedefMarj' => $hedefMarj,
            'importable' => $importable,
            'deadline' => $deadline,
            'gunKalan' => $gunKalan,
            'verdict' => $verdict,
            'cif' => $h['cif'],
            'kayit' => [
                'landed_cost' => $landed,
                'suggested_sale' => $suggested,
                'expected_profit' => $profit,
                'margin' => $margin,
                'importable' => $importable,
                'import_deadline' => $deadline->toDateString(),
                'verdict' => $verdict,
            ],
        ];
    }

    private function aracYakit(string $vergiYakit): string
    {
        return ['benzin_dizel' => 'benzin', 'hibrit' => 'hibrit', 'elektrik' => 'elektrik'][$vergiYakit] ?? 'benzin';
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'kaynak' => ['nullable', 'string', 'max:500'],
            'make' => ['nullable', 'string', 'max:50'],
            'model' => ['required', 'string', 'max:80'],
            'chassis_no' => ['nullable', 'string', 'max:40'],
            'origin' => ['required', Rule::in(array_keys(Vehicle::ORIGINS))],
            'shipping_route' => ['required', 'in:direkt,mersin'],
            'model_year' => ['required', 'integer', 'min:1990', 'max:'.(date('Y') + 1)],
            'first_registration' => ['nullable', 'date', 'before_or_equal:today'],
            'engine_cc' => ['required', 'integer', 'min:0', 'max:20000'],
            'fuel' => ['required', Rule::in(array_keys(Firsat::FUELS))],
            'usage' => ['required', Rule::in(array_keys(Firsat::USAGES))],
            'buy_price' => ['required', 'numeric', 'min:0'],
            'buy_currency' => ['required', Rule::in(array_keys(Para::CURRENCIES))],
            'customs_value' => ['nullable', 'numeric', 'min:0'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'expected_sale' => ['nullable', 'numeric', 'min:0'],
            'kar_marji' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
