<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelurahan_puskesmas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('puskesmas_id')->constrained('puskesmas')->cascadeOnDelete();
            $table->foreignId('kelurahan_id')->constrained('kelurahans')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['puskesmas_id', 'kelurahan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelurahan_puskesmas');
    }
};
