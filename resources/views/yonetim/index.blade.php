@extends('layouts.app')
@section('title', 'Yönetim · Cardino')
@php use App\Models\User; @endphp
@push('head')
<style>
    .box { padding: 18px 20px; margin-bottom: 18px; }
    .box h2 { font-size: 15px; margin: 0 0 14px; }
    table.t { width: 100%; border-collapse: collapse; }
    table.t th, table.t td { text-align: left; padding: .55rem .5rem; border-bottom: 1px solid var(--line); font-size: 14px; vertical-align: top; }
    table.t th { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); }
    table.t td.num, table.t th.num { text-align: right; font-variant-numeric: tabular-nums; }
    .durum-rozet { font-size: 12px; font-weight: 700; border-radius: 999px; padding: 2px 10px; }
    .d-acik { background: #e6f4ea; color: #1e6b37; } .d-kapali { background: #eceef1; color: #6b7280; }
    details.satir { margin-top: 6px; }
    details.satir summary { cursor: pointer; color: var(--brand); font-size: 13px; font-weight: 600; list-style: none; }
    details.satir summary::-webkit-details-marker { display: none; }
    .form-izgara { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; align-items: end; margin-top: 10px; padding: 12px; background: #f7f7f5; border-radius: 10px; }
    .form-izgara label { font-size: 12px; color: var(--muted); margin-bottom: 3px; display: block; }
    .form-izgara input, .form-izgara select { width: 100%; padding: .5rem .6rem; border: 1px solid var(--line); border-radius: 8px; font: 14px var(--font); background: #fff; color: var(--ink); }
    .form-izgara .tam { grid-column: 1 / -1; }
    .onay { display: flex; align-items: center; gap: .4rem; font-size: 13px; }
    .ekle-form { border: 1px dashed var(--line); border-radius: 10px; padding: 14px; margin-top: 14px; }
    .ekle-form h3 { margin: 0 0 10px; font-size: 13px; text-transform: uppercase; letter-spacing: .04em; color: var(--muted); }
    .ipc { font-size: 12px; color: var(--muted); margin-top: 4px; }
</style>
@endpush
@section('content')
<div class="page-head"><h1>Yönetim</h1></div>

@if ($errors->any())
    <div class="error" role="alert" style="background:#fdecea;color:#8a1c12;border:1px solid #f1c4c0;border-radius:8px;padding:.6rem .9rem;margin-bottom:14px">
        {{ $errors->first() }}
    </div>
@endif

<section class="panel box">
    <h2>Galeriler</h2>
    <table class="t">
        <thead><tr><th>Ad</th><th>Şehir</th><th>Telefon</th><th class="num">Kullanıcı</th><th class="num">Araç</th><th>Durum</th></tr></thead>
        <tbody>
        @foreach ($galeriler as $g)
            <tr>
                <td>
                    {{ $g->name }}
                    <details class="satir">
                        <summary>Düzenle</summary>
                        <form method="POST" action="{{ route('yonetim.galeri.update', $g) }}" class="form-izgara">
                            @csrf @method('PUT')
                            <div><label>Ad</label><input name="name" value="{{ $g->name }}" required></div>
                            <div><label>Şehir</label><input name="city" value="{{ $g->city }}"></div>
                            <div><label>Telefon</label><input name="phone" value="{{ $g->phone }}"></div>
                            <div><label class="onay"><input type="checkbox" name="is_active" value="1" @checked($g->is_active)> Aktif</label></div>
                            <div><button class="btn" type="submit">Kaydet</button></div>
                        </form>
                    </details>
                </td>
                <td>{{ $g->city ?: '—' }}</td>
                <td>{{ $g->phone ?: '—' }}</td>
                <td class="num">{{ $g->users_count }}</td>
                <td class="num">{{ $g->vehicles_count }}</td>
                <td><span class="durum-rozet {{ $g->is_active ? 'd-acik' : 'd-kapali' }}">{{ $g->is_active ? 'Aktif' : 'Kapalı' }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="ekle-form">
        <h3>Yeni galeri</h3>
        <form method="POST" action="{{ route('yonetim.galeri.store') }}" class="form-izgara" style="margin-top:0;background:none;padding:0">
            @csrf
            <div><label>Ad</label><input name="name" value="{{ old('name') }}" required></div>
            <div><label>Şehir</label><input name="city" value="{{ old('city') }}"></div>
            <div><label>Telefon</label><input name="phone" value="{{ old('phone') }}"></div>
            <div><button class="btn" type="submit">Ekle</button></div>
        </form>
    </div>
</section>

<section class="panel box">
    <h2>Kullanıcılar</h2>
    <table class="t">
        <thead><tr><th>Kullanıcı adı</th><th>Ad</th><th>Rol</th><th>Galeri</th><th>Durum</th></tr></thead>
        <tbody>
        @foreach ($kullanicilar as $k)
            <tr>
                <td class="mono">{{ $k->username }}</td>
                <td>
                    {{ $k->name }}
                    <details class="satir">
                        <summary>Düzenle</summary>
                        <form method="POST" action="{{ route('yonetim.kullanici.update', $k) }}" class="form-izgara">
                            @csrf @method('PUT')
                            <div><label>Görünen ad</label><input name="name" value="{{ $k->name }}" required></div>
                            <div>
                                <label>Rol</label>
                                <select name="role" @disabled($k->id === auth()->id())>
                                    @foreach (User::ROLES as $key => $label)
                                        <option value="{{ $key }}" @selected($k->role === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label>Galeri</label>
                                <select name="gallery_id" @disabled($k->id === auth()->id())>
                                    <option value="">— (yönetici)</option>
                                    @foreach ($galeriler as $g)
                                        <option value="{{ $g->id }}" @selected($k->gallery_id === $g->id)>{{ $g->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div><label>Yeni şifre</label><input name="password" type="text" placeholder="değiştirmek için yazın"></div>
                            <div>
                                <label class="onay">
                                    <input type="checkbox" name="is_active" value="1" @checked($k->is_active) @disabled($k->id === auth()->id())> Aktif
                                </label>
                            </div>
                            <div><button class="btn" type="submit">Kaydet</button></div>
                            @if ($k->id === auth()->id())
                                <div class="tam ipc">Kendi hesabınızın rolü ve aktifliği buradan değiştirilemez (kilitlenmeyi önler).</div>
                            @endif
                        </form>
                    </details>
                </td>
                <td>{{ $k->roleLabel() }}</td>
                <td>{{ $k->gallery?->name ?? '—' }}</td>
                <td><span class="durum-rozet {{ $k->is_active ? 'd-acik' : 'd-kapali' }}">{{ $k->is_active ? 'Aktif' : 'Kapalı' }}</span></td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="ekle-form">
        <h3>Yeni kullanıcı</h3>
        <form method="POST" action="{{ route('yonetim.kullanici.store') }}" class="form-izgara" style="margin-top:0;background:none;padding:0">
            @csrf
            <div><label>Kullanıcı adı</label><input name="username" value="{{ old('username') }}" required></div>
            <div><label>Görünen ad</label><input name="name" value="{{ old('name') }}" required></div>
            <div>
                <label>Rol</label>
                <select name="role" id="yeni-rol">
                    @foreach (User::ROLES as $key => $label)
                        <option value="{{ $key }}" @selected(old('role', 'personel') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Galeri</label>
                <select name="gallery_id" id="yeni-galeri">
                    <option value="">— (yönetici)</option>
                    @foreach ($galeriler as $g)
                        <option value="{{ $g->id }}" @selected(old('gallery_id') == $g->id)>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Şifre</label><input name="password" type="text" required></div>
            <div><button class="btn" type="submit">Ekle</button></div>
        </form>
    </div>
</section>

<script>
(function () {
    var rol = document.getElementById('yeni-rol'), gal = document.getElementById('yeni-galeri');
    function s() { gal.disabled = (rol.value === 'admin'); if (gal.disabled) gal.value = ''; }
    rol.addEventListener('change', s); s();
})();
</script>
@endsection
