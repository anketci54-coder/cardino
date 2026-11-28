@extends('layouts.app')
@section('title', $firsat->baslik().' · Fırsat')
@php use App\Support\Para;
    $renk = ['al' => ['#16a34a','#dcfce7','#166534'], 'gec' => ['#dc2626','#fee2e2','#991b1b'], 'dusun' => ['#d97706','#fef3c7','#92400e']][$firsat->verdict] ?? ['#64748b','#e2e8f0','#334155'];
@endphp
@push('head')
<style>
    .verdict { border-radius: 16px; padding: 22px 24px; display: flex; align-items: center; gap: 20px; margin-bottom: 18px; }
    .verdict .rozet { font-size: 40px; font-weight: 900; letter-spacing: 1px; padding: 8px 22px; border-radius: 12px; background: #fff; }
    .verdict .aciklama { font-size: 15px; line-height: 1.5; }
    .ozet { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 18px; }
    .kutu { border: 1px solid var(--line); border-radius: 12px; padding: 14px 16px; }
    .kutu .k { font-size: 12px; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; }
    .kutu .v { font-size: 22px; font-weight: 800; font-variant-numeric: tabular-nums; margin-top: 4px; }
    table.kalem { width: 100%; border-collapse: collapse; }
    table.kalem td { padding: .5rem .3rem; border-bottom: 1px solid var(--line); font-size: 14px; }
    table.kalem td.tut { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    table.kalem td .not { color: var(--muted); font-size: 12px; }
    .toplam-sat td { border-top: 2px solid var(--ink); font-weight: 800; font-size: 16px; }
    .pos { color: #16a34a; } .neg { color: #dc2626; }
</style>
@endpush
@section('content')
<div class="page-head">
    <div>
        <a class="muted" href="{{ route('firsat.index') }}" style="text-decoration:none">← Fırsatlar</a>
        <h1 style="margin-top:4px">{{ $firsat->baslik() }}</h1>
        <div class="muted" style="margin-top:4px">{{ $firsat->label('origin') ?? '' }} · {{ $firsat->engine_cc ? number_format($firsat->engine_cc,0,',','.').' cc' : '' }} · {{ \App\Models\Firsat::FUELS[$firsat->fuel] ?? $firsat->fuel }} · {{ $firsat->statusLabel() }}</div>
    </div>
    <div class="actions">
        @if ($firsat->status !== 'alindi' && $firsat->verdict !== 'gec')
            <form method="POST" action="{{ route('firsat.araca', $firsat) }}" onsubmit="return confirm('Bu araç alındı olarak işaretlenip araç kartına çevrilsin mi?')">
                @csrf <button class="btn" type="submit">✓ Alındı — araca çevir</button>
            </form>
        @endif
        @if ($firsat->vehicle_id)
            <a class="btn btn-ghost" href="{{ route('araclar.show', $firsat->vehicle_id) }}">Araç kartı</a>
        @endif
        <form method="POST" action="{{ route('firsat.destroy', $firsat) }}" onsubmit="return confirm('Bu fırsat silinsin mi?')">
            @csrf @method('DELETE') <button class="btn btn-danger" type="submit">Sil</button>
        </form>
    </div>
</div>

@if (session('ok'))<div class="okbox" style="margin-bottom:14px">{{ session('ok') }}</div>@endif

<div class="verdict" style="background:{{ $renk[1] }}">
    <div class="rozet" style="color:{{ $renk[0] }}">{{ $firsat->verdictLabel() }}</div>
    <div class="aciklama" style="color:{{ $renk[2] }}">
        @if (! $firsat->importable)
            <strong>İthal edilemez.</strong> 5 yaş sınırı {{ optional($firsat->import_deadline)->format('d.m.Y') }} tarihinde dolmuş — araç KKTC'ye giremez.
        @elseif ($firsat->verdict === 'al')
            <strong>İyi fırsat.</strong> Beklenen kâr marjı hedefin üzerinde ve 5 yaş sınırı için süre yeterli.
        @elseif ($firsat->verdict === 'gec' && $firsat->expected_profit !== null && $firsat->expected_profit < 0)
            <strong>Zarar riski.</strong> Beklenen satış gümrüklü maliyetin altında.
        @else
            <strong>Dikkatli ol.</strong>
            @if ($firsat->expected_sale === null) Beklenen satışı girersen net AL/GEÇ kararı çıkar.
            @elseif ($sonuc['gunKalan'] <= 120) 5 yaş sınırına {{ $sonuc['gunKalan'] }} gün kaldı — satış süresi kısa.
            @else Kâr marjı hedefin altında. @endif
        @endif
    </div>
</div>

<div class="ozet">
    <div class="kutu"><div class="k">Gümrüklü maliyet</div><div class="v">{{ Para::format($firsat->landed_cost, 'GBP') }}</div></div>
    <div class="kutu"><div class="k">Önerilen satış</div><div class="v">{{ Para::format($firsat->suggested_sale, 'GBP') }}</div></div>
    @if ($firsat->expected_sale !== null)
        <div class="kutu"><div class="k">Beklenen satış</div><div class="v">{{ Para::format($firsat->expected_sale, 'GBP') }}</div></div>
        <div class="kutu"><div class="k">Beklenen kâr</div><div class="v {{ $firsat->expected_profit >= 0 ? 'pos' : 'neg' }}">{{ Para::format($firsat->expected_profit, 'GBP') }}@if($firsat->margin!==null)<span style="font-size:14px"> ({{ rtrim(rtrim(number_format($firsat->margin,1,',','.'),'0'),',') }}%)</span>@endif</div></div>
    @endif
    <div class="kutu"><div class="k">5 yaş sınırı</div><div class="v" style="font-size:18px">{{ optional($firsat->import_deadline)->format('d.m.Y') }}@if($firsat->importable && $sonuc['gunKalan']!==null)<span style="font-size:13px;color:var(--muted)"> · {{ $sonuc['gunKalan'] }} gün</span>@endif</div></div>
</div>

<div class="grid2">
    <section class="panel box">
        <h2>Gümrüklü maliyet dökümü</h2>
        <table class="kalem">
            @foreach ($sonuc['kalemler'] as [$ad, $tutar, $not])
                <tr><td>{{ $ad }}@if($not)<span class="not"> · {{ $not }}</span>@endif</td><td class="tut">{{ Para::format($tutar, 'GBP') }}</td></tr>
            @endforeach
            <tr class="toplam-sat"><td>Toplam</td><td class="tut">{{ Para::format($firsat->landed_cost, 'GBP') }}</td></tr>
        </table>
    </section>

    <div class="stack">
        <section class="panel box">
            <h2>Aday araç</h2>
            <dl>
                <dt>Marka / model</dt><dd>{{ trim(($firsat->make ?? '').' '.$firsat->model) }}</dd>
                <dt>Yıl</dt><dd>{{ $firsat->model_year }}</dd>
                <dt>İlk kayıt</dt><dd>{{ optional($firsat->first_registration)->format('d.m.Y') ?? '— (yıldan tahmin)' }}</dd>
                <dt>Alış</dt><dd>{{ number_format($firsat->buy_price,0,',','.') }} {{ $firsat->buy_currency }}</dd>
                @if($firsat->chassis_no)<dt>Şasi</dt><dd class="mono">{{ $firsat->chassis_no }}</dd>@endif
                @if($firsat->kaynak)<dt>Kaynak</dt><dd style="overflow-wrap:anywhere">{{ $firsat->kaynak }}</dd>@endif
            </dl>
            @if ($firsat->notes)<p style="margin:10px 0 0;white-space:pre-line">{{ $firsat->notes }}</p>@endif
        </section>
    </div>
</div>
@endsection
