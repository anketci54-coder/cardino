<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('firsatlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('galleries')->cascadeOnDelete();

            // Aday araç bilgisi (açık artırma lotu)
            $table->string('kaynak', 500)->nullable();   // açık artırma linki / not
            $table->string('make', 50)->nullable();
            $table->string('model', 80)->nullable();
            $table->string('chassis_no', 40)->nullable();
            $table->string('origin', 20);                // japonya | ingiltere | diger
            $table->string('shipping_route', 20)->default('direkt');
            $table->unsignedSmallInteger('model_year')->nullable();
            $table->date('first_registration')->nullable(); // 5 yaş kuralı (açık artırma kağıdından)
            $table->unsignedSmallInteger('engine_cc')->nullable();
            $table->string('fuel', 20);                  // benzin_dizel | hibrit | elektrik (vergi sınıfı)
            $table->string('usage', 20)->default('ozel'); // ozel | ticari

            // Fiyat girdileri
            $table->decimal('buy_price', 14, 2);         // alış fiyatı
            $table->string('buy_currency', 3);           // JPY | GBP | USD | EUR | TRY
            $table->decimal('customs_value', 14, 2)->nullable(); // gümrük değeri (referans)
            $table->decimal('shipping', 12, 2)->nullable();      // navlun (boşsa varsayılan)
            $table->decimal('expected_sale', 14, 2)->nullable(); // beklenen satış (GBP)

            // Hesaplanan sonuçlar
            $table->decimal('landed_cost', 14, 2)->default(0);   // gümrüklü maliyet (GBP)
            $table->decimal('suggested_sale', 14, 2)->default(0); // önerilen satış (GBP)
            $table->decimal('expected_profit', 14, 2)->nullable(); // beklenen kâr (GBP)
            $table->decimal('margin', 7, 2)->nullable();         // marj %
            $table->boolean('importable')->default(true);        // 5 yaş uygun mu
            $table->date('import_deadline')->nullable();         // 5 yaş sınırı tarihi
            $table->string('verdict', 10)->default('dusun');     // al | gec | dusun
            $table->string('status', 12)->default('izleniyor');  // izleniyor | alindi | gecildi
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['gallery_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('firsatlar');
    }
};
