<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('category', 30);            // alis, acikartirma, ...
            $table->string('description')->nullable();
            $table->decimal('amount', 16, 2);          // orijinal para biriminde
            $table->string('currency', 3);
            $table->decimal('rate', 16, 8);            // 1 birim = x GBP (işlem günü)
            $table->decimal('base_amount', 14, 2);     // GBP karşılığı
            $table->date('spent_on')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['vehicle_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_costs');
    }
};
