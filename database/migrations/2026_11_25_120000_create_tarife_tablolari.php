<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Gümrük vergisi oranları: yakıt türü × kullanım (özel/ticari) → oran %
        Schema::create('vergi_oranlari', function (Blueprint $table) {
            $table->id();
            $table->string('yakit', 20);       // benzin_dizel | elektrik_hibrit
            $table->string('kullanim', 20);    // ozel | ticari
            $table->decimal('oran', 6, 3);     // yüzde (ör. 6.000)
            $table->timestamps();
            $table->unique(['yakit', 'kullanim']);
        });

        // Kayıt / ruhsat harcı tarifesi: motor hacmi (cc) ve yaş aralığına göre
        Schema::create('kayit_tarifeleri', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('min_cc')->default(0);
            $table->unsignedInteger('max_cc')->nullable();   // boş = üst sınır yok
            $table->unsignedInteger('min_yas')->default(0);
            $table->unsignedInteger('max_yas')->nullable();  // boş = üst sınır yok
            $table->decimal('tutar_gbp', 12, 2)->nullable(); // sabit tutar (GBP)
            $table->decimal('oran', 6, 3)->nullable();       // ya da araç değerinin yüzdesi
            $table->string('aciklama')->nullable();
            $table->unsignedInteger('sira')->default(0);
            $table->timestamps();
        });

        // Genel ayarlar: anahtar-değer (KDV, varsayılan masraflar, kâr marjı)
        Schema::create('gumruk_ayarlari', function (Blueprint $table) {
            $table->id();
            $table->string('anahtar', 50)->unique();
            $table->string('deger')->nullable();
            $table->string('aciklama')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vergi_oranlari');
        Schema::dropIfExists('kayit_tarifeleri');
        Schema::dropIfExists('gumruk_ayarlari');
    }
};
