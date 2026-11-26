<?php

namespace App\Console\Commands;

use App\Models\ExchangeRate;
use App\Support\Para;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Para kurlarını Avrupa Merkez Bankası'nın (ECB) resmi günlük referans
 * kurlarından otomatik çeker. ECB EUR bazlıdır; çapraz kurla GBP'ye çevrilir.
 * Kaynak: https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml (açık, resmi).
 */
class KurGuncelleOtomatik extends Command
{
    protected $signature = 'cardino:kur-guncelle {--tarih= : Kayıt tarihi (YYYY-AA-GG), boşsa bugün}';

    protected $description = 'Para kurlarını ECB resmi referans kurlarından otomatik günceller (GBP tabanlı)';

    private const KAYNAK = 'https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml';

    public function handle(): int
    {
        try {
            $resp = Http::timeout(25)->retry(2, 2000)->get(self::KAYNAK);
        } catch (\Throwable $e) {
            return $this->hata('ECB bağlantısı başarısız: '.$e->getMessage());
        }
        if (! $resp->ok()) {
            return $this->hata('ECB yanıtı HTTP '.$resp->status());
        }

        $xml = @simplexml_load_string($resp->body());
        if ($xml === false) {
            return $this->hata('ECB XML çözümlenemedi.');
        }
        $xml->registerXPathNamespace('ex', 'http://www.ecb.int/vocabulary/2002-08-01/eurofxref');
        $nodes = $xml->xpath('//ex:Cube[@currency]');

        // EUR -> para birimi kurları
        $eur = [];
        foreach ($nodes as $n) {
            $eur[strtoupper((string) $n['currency'])] = (float) $n['rate'];
        }
        $eurGbp = $eur['GBP'] ?? null;
        if (! $eurGbp || $eurGbp <= 0) {
            return $this->hata('ECB verisinde GBP kuru yok.');
        }

        $tarih = $this->option('tarih') ? Carbon::parse($this->option('tarih')) : Carbon::today();
        $yazilan = [];

        foreach (array_keys(Para::CURRENCIES) as $cur) {
            if ($cur === Para::BASE) {
                continue; // GBP taban, kur 1
            }
            // 1 birim <cur> kaç GBP: (EUR->GBP) / (EUR->cur); EUR için doğrudan EUR->GBP
            if ($cur === 'EUR') {
                $toGbp = $eurGbp;
            } else {
                if (empty($eur[$cur])) {
                    $this->warn("ECB'de {$cur} kuru bulunamadı, atlandı.");

                    continue;
                }
                $toGbp = $eurGbp / $eur[$cur];
            }
            $toGbp = round($toGbp, 8);
            ExchangeRate::updateOrCreate(
                ['currency' => $cur, 'as_of' => $tarih->toDateString()],
                ['rate_to_gbp' => $toGbp]
            );
            $yazilan[$cur] = $toGbp;
        }

        if ($yazilan === []) {
            return $this->hata('Hiçbir kur yazılamadı.');
        }

        $ozet = collect($yazilan)->map(fn ($r, $c) => "1 {$c} = {$r} GBP")->implode(' · ');
        $this->info("Kurlar güncellendi ({$tarih->format('d.m.Y')}): {$ozet}");
        Log::info('Kur otomatik güncellendi', ['tarih' => $tarih->toDateString(), 'kurlar' => $yazilan]);

        return self::SUCCESS;
    }

    private function hata(string $mesaj): int
    {
        $this->error($mesaj);
        Log::warning('Kur otomatik güncelleme hatası: '.$mesaj);

        return self::FAILURE;
    }
}
