<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->string('kind', 10);                 // foto | belge
            $table->string('doc_type', 20)->nullable(); // belge için: fatura, konsimento, ...
            $table->string('original_name');
            $table->string('path');                     // local diskte göreli yol
            $table->string('mime', 100);
            $table->unsignedBigInteger('size');         // bayt
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['vehicle_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_files');
    }
};
