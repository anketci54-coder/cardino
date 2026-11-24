<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency', 3);            // JPY, GBP, USD, EUR, TRY
            $table->decimal('rate_to_gbp', 16, 8);     // 1 birim bu para = x GBP
            $table->date('as_of');
            $table->timestamps();
            $table->unique(['currency', 'as_of']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
