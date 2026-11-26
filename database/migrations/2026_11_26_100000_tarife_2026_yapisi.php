<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 2026 KKTC gümrük yapısı: yakıt×kullanım için ayrı gümrük/kayıt/KDV oranları
 * ve motor hacmine (cc) bağlı FİF kademe tablosu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vergi_oranlari', function (Blueprint $table) {
            $table->decimal('gumruk_orani', 6, 3)->default(0)->after('kullanim');
            $table->decimal('kayit_orani', 6, 3)->default(0)->after('gumruk_orani');
            $table->decimal('kdv_orani', 6, 3)->default(0)->after('kayit_orani');
        });

        // FİF (Fiyat İstikrar Fonu): yakıt + motor hacmi kademesine göre
        Schema::create('fif_tarifeleri', function (Blueprint $table) {
            $table->id();
            $table->string('yakit', 20);
            $table->unsignedInteger('min_cc')->default(0);
            $table->unsignedInteger('max_cc')->nullable();
            $table->decimal('oran', 6, 3);
            $table->unsignedInteger('sira')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fif_tarifeleri');
        Schema::table('vergi_oranlari', function (Blueprint $table) {
            $table->dropColumn(['gumruk_orani', 'kayit_orani', 'kdv_orani']);
        });
    }
};
