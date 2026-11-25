<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function index(): View
    {
        // Durum dağılımı
        $sayim = Vehicle::query()->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');
        $eldeDurumlar = ['yolda', 'gumrukte', 'stokta'];
        $eldeAdet = collect($eldeDurumlar)->sum(fn ($s) => (int) ($sayim[$s] ?? 0));

        // Bağlı sermaye: satılmamış araçların toplam maliyeti (GBP)
        $elde = Vehicle::query()->whereIn('status', $eldeDurumlar)->withSum('costs', 'base_amount')->get();
        $bagliSermaye = (float) $elde->sum(fn ($v) => (float) ($v->costs_sum_base_amount ?? 0));

        // Bu ay satış ve kâr
        $ayBasi = Carbon::now()->startOfMonth();
        $aySonu = Carbon::now()->endOfMonth();
        $buAy = Vehicle::query()
            ->where('status', 'satildi')
            ->whereBetween('sold_on', [$ayBasi, $aySonu])
            ->withSum('costs', 'base_amount')
            ->get();
        $buAyAdet = $buAy->count();
        $buAyCiro = (float) $buAy->sum(fn ($v) => (float) ($v->sale_base ?? 0));
        $buAyKar = (float) $buAy->sum(fn ($v) => (float) ($v->sale_base ?? 0) - (float) ($v->costs_sum_base_amount ?? 0));

        // 5 yaş sınırı yaklaşan / dolmuş (hâlâ yolda veya gümrükte)
        $dikkat = Vehicle::query()
            ->whereIn('status', ['yolda', 'gumrukte'])
            ->whereNotNull('first_registration')
            ->get()
            ->map(function ($v) {
                $v->setAttribute('kalan_gun', (int) Carbon::today()->diffInDays($v->importDeadline(), false));

                return $v;
            })
            ->filter(fn ($v) => $v->kalan_gun <= 120)
            ->sortBy('kalan_gun')
            ->take(8)
            ->values();

        // Son eklenen araçlar
        $sonAraclar = Vehicle::query()->with('gallery')->latest()->take(6)->get();

        return view('panel', compact('sayim', 'eldeAdet', 'bagliSermaye', 'buAyAdet', 'buAyCiro', 'buAyKar', 'dikkat', 'sonAraclar'));
    }
}
