<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pasien_id')->constrained('pasiens')->cascadeOnDelete();
            $table->foreignId('faskes_id')->constrained('faskes')->cascadeOnDelete();
            $table->enum('visit_type', ['investigasi_kasus', 'kunjungan_rumah', 'kunjungan_mangkir']);
            $table->date('visit_date');
            $table->text('findings')->nullable();
            $table->text('follow_up')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_source')->nullable(); // 'gps' atau 'maps'
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_results');
    }
};
