<?php

namespace App\Support;

use App\Models\Vehicle;

/**
 * Araç için şık, özgün bir tanıtım görseli (SVG) üretir — gerçek fotoğraf yerine
 * simülasyon/placeholder olarak. Rengine ve tipine (SUV / salon) göre çizilir.
 */
class AracGorsel
{
    /** Türkçe renk adı → gövde rengi (hex) */
    private const RENKLER = [
        'Siyah' => '#1b1d21', 'Beyaz' => '#eef1f4', 'Gri' => '#8b9099', 'Gümüş' => '#c3c8ce',
        'Lacivert' => '#1f2b48', 'Mavi' => '#2563eb', 'Kırmızı' => '#b4232a', 'Turuncu' => '#e2622a',
        'Yeşil' => '#1f7a4d', 'Bordo' => '#6d1f2b',
    ];

    private const SUV = ['GLC', 'GLE', 'GLA', 'X3', 'X5', 'NX', 'Harrier', 'Evoque', 'Qashqai', 'Juke', 'RX', 'GLB'];

    public static function svg($arac): string
    {
        $renk = self::RENKLER[$arac->color] ?? '#6b7280';
        [$govdeUst, $govdeAlt] = self::tonlar($renk);
        $acik = self::acikMi($renk);
        $suv = self::suvMu($arac->model);

        $marka = htmlspecialchars((string) $arac->make, ENT_QUOTES);
        $model = htmlspecialchars((string) $arac->model, ENT_QUOTES);
        $yil = (int) $arac->model_year;
        $cc = $arac->engine_cc ? number_format($arac->engine_cc, 0, ',', '.').' cc' : '';
        $yakit = $arac->label('fuel');
        $bayrak = $arac->origin === 'japonya' ? 'JP' : ($arac->origin === 'ingiltere' ? 'UK' : '');
        $camYansima = $acik ? '#b9c6d6' : '#d9e6f2';

        $araba = $suv ? self::suvCizim($govdeUst, $govdeAlt, $camYansima) : self::salonCizim($govdeUst, $govdeAlt, $camYansima);

        $pills = self::pills(array_filter([$yil ?: null, $cc ?: null, $yakit !== '—' ? $yakit : null]));

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 675" width="1200" height="675" role="img" aria-label="{$marka} {$model}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#0f172a"/>
      <stop offset="1" stop-color="#1e2a3f"/>
    </linearGradient>
    <radialGradient id="glow" cx="0.78" cy="0.22" r="0.6">
      <stop offset="0" stop-color="{$renk}" stop-opacity="0.38"/>
      <stop offset="1" stop-color="{$renk}" stop-opacity="0"/>
    </radialGradient>
    <linearGradient id="govde" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0" stop-color="{$govdeUst}"/>
      <stop offset="1" stop-color="{$govdeAlt}"/>
    </linearGradient>
  </defs>

  <rect width="1200" height="675" fill="url(#bg)"/>
  <rect width="1200" height="675" fill="url(#glow)"/>

  <!-- zemin gölgesi -->
  <ellipse cx="600" cy="540" rx="430" ry="36" fill="#000" opacity="0.28"/>

  {$araba}

  <!-- metin -->
  <text x="70" y="108" fill="#8ea2bd" font-family="Arial, Helvetica, sans-serif" font-size="30" letter-spacing="7" font-weight="700">{$marka}</text>
  <text x="68" y="168" fill="#f6f8fb" font-family="Arial, Helvetica, sans-serif" font-size="56" font-weight="800">{$model}</text>

  <text x="1130" y="60" text-anchor="end" fill="#64748b" font-family="Arial, Helvetica, sans-serif" font-size="26" font-weight="800" letter-spacing="2">pilotcar</text>
  <text x="1130" y="96" text-anchor="end" fill="#94a3b8" font-family="Arial, Helvetica, sans-serif" font-size="22" font-weight="700" letter-spacing="3">{$bayrak}</text>

  {$pills}
</svg>
SVG;
    }

    private static function suvMu(?string $model): bool
    {
        foreach (self::SUV as $k) {
            if ($model && stripos($model, $k) !== false) {
                return true;
            }
        }

        return false;
    }

    private static function tonlar(string $hex): array
    {
        return [self::karistir($hex, '#ffffff', 0.32), self::karistir($hex, '#000000', 0.22)];
    }

    private static function acikMi(string $hex): bool
    {
        [$r, $g, $b] = self::rgb($hex);

        return (0.299 * $r + 0.587 * $g + 0.114 * $b) > 150;
    }

    private static function rgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    private static function karistir(string $a, string $b, float $t): string
    {
        [$ar, $ag, $ab] = self::rgb($a);
        [$br, $bg, $bb] = self::rgb($b);
        $m = fn ($x, $y) => (int) round($x + ($y - $x) * $t);

        return sprintf('#%02x%02x%02x', $m($ar, $br), $m($ag, $bg), $m($ab, $bb));
    }

    /** Salon / hatchback yan silueti */
    private static function salonCizim(string $ust, string $alt, string $cam): string
    {
        return <<<SVG
<g>
    <path d="M250 468 C250 452 262 444 286 440 L360 430 C388 388 440 368 520 364 L740 360 C806 360 850 384 884 426 L980 440 C1012 446 1026 456 1026 470 L1026 486 C1026 494 1020 500 1010 500 L266 500 C256 500 250 494 250 486 Z" fill="url(#govde)" stroke="#aebccd" stroke-opacity="0.28" stroke-width="2.5"/>
    <path d="M404 426 C430 398 470 386 524 384 L706 382 C760 384 792 402 820 430 Z" fill="{$cam}" opacity="0.6" stroke="#dfeaf5" stroke-opacity="0.25" stroke-width="2"/>
    <rect x="250" y="470" width="776" height="8" fill="#000" opacity="0.18"/>
    <circle cx="372" cy="500" r="62" fill="#111317"/><circle cx="372" cy="500" r="30" fill="#2a2f37"/><circle cx="372" cy="500" r="10" fill="#5b616b"/>
    <circle cx="872" cy="500" r="62" fill="#111317"/><circle cx="872" cy="500" r="30" fill="#2a2f37"/><circle cx="872" cy="500" r="10" fill="#5b616b"/>
    <rect x="1008" y="452" width="20" height="14" rx="4" fill="#ffe08a" opacity="0.9"/>
    <rect x="252" y="452" width="16" height="12" rx="4" fill="#ff5b5b" opacity="0.85"/>
</g>
SVG;
    }

    /** SUV yan silueti (daha yüksek, dik kabin, büyük tekerlek) */
    private static function suvCizim(string $ust, string $alt, string $cam): string
    {
        return <<<SVG
<g>
    <path d="M240 476 C240 456 252 446 276 442 L350 432 C372 374 420 346 500 342 L742 340 C820 342 866 372 892 432 L972 444 C1006 450 1022 460 1022 478 L1022 498 C1022 506 1016 512 1006 512 L256 512 C246 512 240 506 240 498 Z" fill="url(#govde)" stroke="#aebccd" stroke-opacity="0.28" stroke-width="2.5"/>
    <path d="M396 430 C416 390 452 368 506 366 L724 364 C778 366 812 388 842 430 Z" fill="{$cam}" opacity="0.6" stroke="#dfeaf5" stroke-opacity="0.25" stroke-width="2"/>
    <line x1="610" y1="366" x2="610" y2="430" stroke="#0f172a" stroke-width="6" opacity="0.35"/>
    <rect x="240" y="480" width="782" height="9" fill="#000" opacity="0.18"/>
    <circle cx="368" cy="512" r="74" fill="#111317"/><circle cx="368" cy="512" r="36" fill="#2a2f37"/><circle cx="368" cy="512" r="12" fill="#5b616b"/>
    <circle cx="878" cy="512" r="74" fill="#111317"/><circle cx="878" cy="512" r="36" fill="#2a2f37"/><circle cx="878" cy="512" r="12" fill="#5b616b"/>
    <rect x="1002" y="456" width="22" height="15" rx="4" fill="#ffe08a" opacity="0.9"/>
    <rect x="242" y="456" width="17" height="13" rx="4" fill="#ff5b5b" opacity="0.85"/>
</g>
SVG;
    }

    /** Alt bilgi rozetleri */
    private static function pills(array $etiketler): string
    {
        $x = 70;
        $y = 600;
        $ty = $y + 30;
        $out = '';
        foreach ($etiketler as $t) {
            $t = htmlspecialchars((string) $t, ENT_QUOTES);
            $w = 34 + mb_strlen($t) * 15;
            $cx = $x + intdiv($w, 2);
            $out .= <<<SVG
<g><rect x="{$x}" y="{$y}" width="{$w}" height="46" rx="23" fill="#ffffff" opacity="0.10"/>
<text x="{$cx}" y="{$ty}" text-anchor="middle" fill="#e6edf6" font-family="Arial, Helvetica, sans-serif" font-size="24" font-weight="700">{$t}</text></g>
SVG;
            $x += $w + 16;
        }

        return $out;
    }
}
