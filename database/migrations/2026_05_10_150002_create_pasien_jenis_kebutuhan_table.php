<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasien_jenis_kebutuhan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained('pasiens')->cascadeOnDelete();
            $table->foreignId('jenis_kebutuhan_id')->constrained('jenis_kebutuhans')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['pasien_id', 'jenis_kebutuhan_id'], 'pasien_jk_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasien_jenis_kebutuhan');
    }
};
