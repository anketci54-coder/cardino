<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Geliş güzergâhı: doğrudan KKTC limanı mı, Mersin aktarmalı mı
            $table->string('shipping_route', 20)->nullable()->after('origin'); // direkt | mersin
            // Satış
            $table->date('sold_on')->nullable()->after('purchase_date');
            $table->string('buyer_name')->nullable()->after('sold_on');
            $table->decimal('sale_amount', 16, 2)->nullable()->after('buyer_name');
            $table->string('sale_currency', 3)->nullable()->after('sale_amount');
            $table->decimal('sale_rate', 16, 8)->nullable()->after('sale_currency');  // 1 birim = x GBP
            $table->decimal('sale_base', 14, 2)->nullable()->after('sale_rate');       // GBP karşılığı
            $table->boolean('is_trade_in')->default(false)->after('sale_base');         // takasla mı geldi
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['shipping_route', 'sold_on', 'buyer_name', 'sale_amount', 'sale_currency', 'sale_rate', 'sale_base', 'is_trade_in']);
        });
    }
};
