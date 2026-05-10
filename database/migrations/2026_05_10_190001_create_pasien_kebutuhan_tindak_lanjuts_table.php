<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasien_kebutuhan_tindak_lanjuts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_kebutuhan_id')->constrained('pasien_kebutuhans')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->text('keterangan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasien_kebutuhan_tindak_lanjuts');
    }
};
