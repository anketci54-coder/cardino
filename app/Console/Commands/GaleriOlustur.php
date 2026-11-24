<?php

namespace App\Console\Commands;

use App\Models\Gallery;
use Illuminate\Console\Command;

class GaleriOlustur extends Command
{
    protected $signature = 'cardino:galeri {ad : Galeri adı} {--sehir=} {--telefon=}';

    protected $description = 'Yeni galeri oluşturur ve numarasını yazar';

    public function handle(): int
    {
        $g = Gallery::firstOrCreate(
            ['name' => $this->argument('ad')],
            ['city' => $this->option('sehir'), 'phone' => $this->option('telefon')]
        );
        $this->info("Galeri hazır: #{$g->id} {$g->name}");

        return self::SUCCESS;
    }
}
