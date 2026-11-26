<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('vergi_oranlari', 'oran')) {
            Schema::table('vergi_oranlari', function (Blueprint $table) {
                $table->dropColumn('oran');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('vergi_oranlari', 'oran')) {
            Schema::table('vergi_oranlari', function (Blueprint $table) {
                $table->decimal('oran', 6, 3)->default(0);
            });
        }
    }
};
