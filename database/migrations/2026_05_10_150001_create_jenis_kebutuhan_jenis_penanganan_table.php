<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_kebutuhan_jenis_penanganan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_kebutuhan_id')->constrained('jenis_kebutuhans')->cascadeOnDelete();
            $table->foreignId('jenis_penanganan_id')->constrained('jenis_penanganans')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['jenis_kebutuhan_id', 'jenis_penanganan_id'], 'jk_jp_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_kebutuhan_jenis_penanganan');
    }
};
