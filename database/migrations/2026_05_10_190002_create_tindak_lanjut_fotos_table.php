<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tindak_lanjut_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tindak_lanjut_id')->constrained('pasien_kebutuhan_tindak_lanjuts')->cascadeOnDelete();
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjut_fotos');
    }
};
