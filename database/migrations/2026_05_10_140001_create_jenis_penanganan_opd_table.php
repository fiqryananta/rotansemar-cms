<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_penanganan_opd', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_penanganan_id')->constrained('jenis_penanganans')->cascadeOnDelete();
            $table->foreignId('opd_id')->constrained('opds')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['jenis_penanganan_id', 'opd_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_penanganan_opd');
    }
};
