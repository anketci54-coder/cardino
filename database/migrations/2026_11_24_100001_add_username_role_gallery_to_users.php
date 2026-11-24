<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('id');
            // admin: Cardino yöneticisi (tüm galeriler) · sahip: galeri sahibi · personel: galeri çalışanı
            $table->string('role')->default('personel')->after('password');
            $table->foreignId('gallery_id')->nullable()->after('role')->constrained('galleries')->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('gallery_id');
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('gallery_id');
            $table->dropColumn(['username', 'role', 'is_active']);
        });
    }
};
