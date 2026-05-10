<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_pickups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained('pasiens')->cascadeOnDelete();
            $table->foreignId('faskes_id')->constrained('faskes')->cascadeOnDelete();
            $table->date('scheduled_date');
            $table->enum('status', ['terrealisasi', 'terrealisasi_obat_terakhir', 'tidak_datang', 'meninggal', 'pindah_fasyankes'])->nullable();
            $table->date('actual_date')->nullable();
            $table->date('next_pickup_date')->nullable();
            $table->date('death_date')->nullable();
            $table->foreignId('target_faskes_id')->nullable()->constrained('faskes')->nullOnDelete();
            $table->boolean('is_outside_city')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_pickups');
    }
};
