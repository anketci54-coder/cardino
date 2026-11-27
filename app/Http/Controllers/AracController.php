<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Vehicle;
use App\Models\VehicleCost;
use App\Models\VergiOrani;
use App\Support\MaliyetHesaplayici;
use App\Support\Mense;
use App\Support\Para;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AracController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q'));
        $vehicles = Vehicle::query()
            ->with('gallery')
            ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x
                ->where('chassis_no', 'like', "%{$q}%")
                ->orWhere('engine_no', 'like', "%{$q}%")
                ->orWhere('make', 'like', "%{$q}%")
                ->orWhere('model', 'like', "%{$q}%")))
            ->when($request->filled('durum'), fn ($w) => $w->where('status', $request->query('durum')))
            ->when($request->filled('mense'), fn ($w) => $w->where('origin', $request->query('mense')))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $counts = Vehicle::query()->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('araclar.index', compact('vehicles', 'q', 'counts'));
    }

    public function create(Request $request): View
    {
        $kostTaslak = null;
        $vehicle = new Vehicle(['status' => 'stokta']);

        // Maliyet tahmininden gelindiyse (oturumdaki taslak) formu önceden doldur
        if ($request->boolean('tahmin') && $request->session()->has('maliyet_taslak')) {
            $kostTaslak = $request->session()->get('maliyet_taslak');
            $onDolu = $request->session()->get('tahmin_arac', []);
            $vehicle = new Vehicle(['status' => 'stokta'] + array_intersect_key($onDolu, array_flip([
                'fuel', 'engine_cc', 'model_year', 'shipping_route',
            ])));
        }

        return view('araclar.form', [
            'vehicle' => $vehicle,
            'galleries' => $this->galleriesFor($request),
            'kostTaslak' => $kostTaslak,
        ]);
    }

    /** Maliyet tahminini araç kartına dönüştürme: hesaplar, oturuma koyar, create formuna yönlendirir. */
    public function tahminForm(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'alis_fiyat' => ['required', 'numeric', 'min:0'],
            'alis_para' => ['required', 'in:'.implode(',', array_keys(Para::CURRENCIES))],
            'yakit' => ['required', 'in:'.implode(',', array_keys(VergiOrani::YAKITLAR))],
            'kullanim' => ['required', 'in:'.implode(',', array_keys(VergiOrani::KULLANIMLAR))],
            'motor_cc' => ['required', 'integer', 'min:0', 'max:20000'],
            'model_yili' => ['required', 'integer', 'min:1950', 'max:'.(date('Y') + 1)],
            'rota' => ['required', 'in:direkt,mersin'],
            'gumruk_degeri' => ['nullable', 'numeric', 'min:0'],
            'navlun' => ['nullable', 'numeric', 'min:0'],
            'kar_marji' => ['nullable', 'numeric', 'min:0', 'max:500'],
        ]);

        $h = MaliyetHesaplayici::hesapla($data);

        // Kalemleri + araç ön dolgusunu oturumda tut (istemciye güvenmeyiz); create ekranına geç
        $request->session()->put('maliyet_taslak', $h['kostlar']);
        $request->session()->put('tahmin_arac', $h['arac']);

        return redirect()->route('araclar.create', ['tahmin' => 1]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = $request->user()->id;
        $vehicle = Vehicle::create($data);

        // Tahminden gelen maliyet kalemlerini ekle (oturumdaki taslaktan, istemci verisine güvenmeden)
        if ($request->boolean('tahminden')) {
            $this->tahminKalemleriniEkle($request, $vehicle);
        }

        return redirect()->route('araclar.show', $vehicle)->with('ok', 'Araç kaydedildi.');
    }

    /** Oturumdaki tahmin taslağından VehicleCost kayıtlarını oluşturur. */
    private function tahminKalemleriniEkle(Request $request, Vehicle $vehicle): void
    {
        $taslak = $request->session()->pull('maliyet_taslak');
        if (! is_array($taslak)) {
            return;
        }
        $bugun = Carbon::today();
        $gecerliKategori = array_keys(VehicleCost::CATEGORIES);
        $gecerliPara = array_keys(Para::CURRENCIES);

        foreach ($taslak as $k) {
            if (! is_array($k) || ! in_array($k['category'] ?? null, $gecerliKategori, true)) {
                continue;
            }
            $tutar = round((float) ($k['amount'] ?? 0), 2);
            if ($tutar <= 0) {
                continue;
            }
            $para = in_array($k['currency'] ?? null, $gecerliPara, true) ? $k['currency'] : Para::BASE;
            $kur = $para === Para::BASE ? 1.0 : (float) ($k['rate'] ?? 0);
            $base = round((float) ($k['base'] ?? ($tutar * $kur)), 2);

            $vehicle->costs()->create([
                'category' => $k['category'],
                'description' => $k['description'] ?? null,
                'amount' => $tutar,
                'currency' => $para,
                'rate' => $kur,
                'base_amount' => $base,
                'spent_on' => $bugun,
                'created_by' => $request->user()->id,
            ]);
        }
    }

    public function show(Vehicle $arac): View
    {
        return view('araclar.show', [
            'vehicle' => $arac->load(['gallery', 'costs' => fn ($q) => $q->orderBy('spent_on')->orderBy('id'), 'files']),
            'rates' => \App\Models\ExchangeRate::latest(),
        ]);
    }

    public function edit(Request $request, Vehicle $arac): View
    {
        return view('araclar.form', ['vehicle' => $arac, 'galleries' => $this->galleriesFor($request)]);
    }

    public function update(Request $request, Vehicle $arac): RedirectResponse
    {
        $arac->update($this->validated($request, $arac));

        return redirect()->route('araclar.show', $arac)->with('ok', 'Değişiklikler kaydedildi.');
    }

    public function destroy(Vehicle $arac): RedirectResponse
    {
        $arac->delete();

        return redirect()->route('araclar.index')->with('ok', 'Araç silindi.');
    }

    private function galleriesFor(Request $request)
    {
        return $request->user()->isAdmin() ? Gallery::orderBy('name')->get() : collect();
    }

    private function validated(Request $request, ?Vehicle $vehicle = null): array
    {
        $request->merge([
            'chassis_no' => Mense::normalize($request->input('chassis_no')),
            'engine_no' => $request->filled('engine_no') ? Mense::normalize($request->input('engine_no')) : null,
            'mileage_km' => $request->filled('mileage_km') ? preg_replace('/\D/', '', $request->input('mileage_km')) : null,
        ]);
        if (! $request->filled('origin')) {
            $request->merge(['origin' => Mense::guess($request->input('chassis_no')) ?? 'diger']);
        }

        $user = $request->user();
        $galleryId = $user->isAdmin() ? $request->input('gallery_id', $vehicle?->gallery_id) : $user->gallery_id;

        $data = $request->validate([
            'gallery_id' => $user->isAdmin() ? ['required', 'exists:galleries,id'] : ['nullable'],
            'chassis_no' => ['required', 'string', 'max:40',
                Rule::unique('vehicles', 'chassis_no')->where('gallery_id', $galleryId)->whereNull('deleted_at')->ignore($vehicle?->id)],
            'engine_no' => ['nullable', 'string', 'max:40'],
            'origin' => ['required', Rule::in(array_keys(Vehicle::ORIGINS))],
            'shipping_route' => ['nullable', Rule::in(array_keys(Vehicle::ROUTES))],
            'make' => ['required', 'string', 'max:50'],
            'model' => ['required', 'string', 'max:80'],
            'grade' => ['nullable', 'string', 'max:80'],
            'model_year' => ['nullable', 'integer', 'between:1950,'.(date('Y') + 1)],
            'first_registration' => ['nullable', 'date', 'before_or_equal:today'],
            'color' => ['nullable', 'string', 'max:40'],
            'mileage_km' => ['nullable', 'integer', 'min:0', 'max:2000000'],
            'engine_cc' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'fuel' => ['nullable', Rule::in(array_keys(Vehicle::FUELS))],
            'transmission' => ['nullable', Rule::in(array_keys(Vehicle::TRANSMISSIONS))],
            'status' => ['required', Rule::in(array_keys(Vehicle::STATUSES))],
            'purchase_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ], [
            'gallery_id.required' => 'Galeri seçin.',
            'chassis_no.required' => 'Şasi numarasını yazın.',
            'chassis_no.unique' => 'Bu şasi numarası galeride zaten kayıtlı.',
            'make.required' => 'Markayı yazın.',
            'model.required' => 'Modeli yazın.',
            'model_year.between' => 'Model yılı geçerli değil.',
            'first_registration.before_or_equal' => 'İlk kayıt tarihi bugünden ileri olamaz.',
            'mileage_km.integer' => 'Kilometreyi sayı olarak yazın.',
            'engine_cc.integer' => 'Motor hacmini sayı olarak yazın (cc).',
        ]);

        if (! $user->isAdmin()) {
            unset($data['gallery_id']);
        }

        return $data;
    }
}
