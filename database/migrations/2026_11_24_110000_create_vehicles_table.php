<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('galleries')->cascadeOnDelete();
            $table->string('chassis_no', 40);
            $table->string('engine_no', 40)->nullable();
            $table->string('origin', 20);              // japonya | ingiltere | diger
            $table->string('make', 50);
            $table->string('model', 80);
            $table->string('grade', 80)->nullable();   // paket / donanım
            $table->unsignedSmallInteger('model_year')->nullable();
            $table->date('first_registration')->nullable(); // 5 yaş kuralı için
            $table->string('color', 40)->nullable();
            $table->unsignedInteger('mileage_km')->nullable();
            $table->unsignedSmallInteger('engine_cc')->nullable();
            $table->string('fuel', 20)->nullable();     // benzin | dizel | hibrit | elektrik
            $table->string('transmission', 20)->nullable(); // otomatik | manuel
            $table->string('status', 20)->default('stokta'); // yolda | gumrukte | stokta | satildi
            $table->date('purchase_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['gallery_id', 'chassis_no']);
            $table->index(['gallery_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
