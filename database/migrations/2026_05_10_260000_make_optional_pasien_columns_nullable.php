<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pasiens', function (Blueprint $table) {
            $table->string('coordinates')->nullable()->change();
            $table->enum('economic_status', ['miskin', 'sederhana', 'mampu'])->nullable()->change();
            $table->string('family_head_name')->nullable()->change();
            $table->string('family_head_nik', 16)->nullable()->change();
            $table->enum('family_income_range', ['<1000000', '>1000000'])->nullable()->change();
            $table->enum('patient_relationship', ['pasien-sendiri', 'orang-tua', 'anak'])->nullable()->change();

            $table->unsignedInteger('tb_so_ro')->nullable()->change();
            $table->date('treatment_start_date')->nullable()->change();
            $table->date('visit_date')->nullable()->change();
            $table->date('information_date')->nullable()->change();
            $table->enum('treatment_status', ['terlaksana', 'belum'])->nullable()->change();
            $table->enum('nutritional_status', ['normal', 'tidak-normal'])->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('pasiens')
            ->whereNull('coordinates')
            ->update(['coordinates' => '0,0']);

        DB::table('pasiens')
            ->whereNull('economic_status')
            ->update(['economic_status' => 'sederhana']);

        DB::table('pasiens')
            ->whereNull('family_head_name')
            ->update(['family_head_name' => '-']);

        DB::table('pasiens')
            ->whereNull('family_head_nik')
            ->update(['family_head_nik' => '0000000000000000']);

        DB::table('pasiens')
            ->whereNull('family_income_range')
            ->update(['family_income_range' => '<1000000']);

        DB::table('pasiens')
            ->whereNull('patient_relationship')
            ->update(['patient_relationship' => 'pasien-sendiri']);

        DB::table('pasiens')
            ->whereNull('tb_so_ro')
            ->update(['tb_so_ro' => 0]);

        DB::table('pasiens')
            ->whereNull('treatment_start_date')
            ->update(['treatment_start_date' => now()->toDateString()]);

        DB::table('pasiens')
            ->whereNull('visit_date')
            ->update(['visit_date' => now()->toDateString()]);

        DB::table('pasiens')
            ->whereNull('information_date')
            ->update(['information_date' => now()->toDateString()]);

        DB::table('pasiens')
            ->whereNull('treatment_status')
            ->update(['treatment_status' => 'belum']);

        DB::table('pasiens')
            ->whereNull('nutritional_status')
            ->update(['nutritional_status' => 'normal']);

        Schema::table('pasiens', function (Blueprint $table) {
            $table->string('coordinates')->nullable(false)->change();
            $table->enum('economic_status', ['miskin', 'sederhana', 'mampu'])->nullable(false)->change();
            $table->string('family_head_name')->nullable(false)->change();
            $table->string('family_head_nik', 16)->nullable(false)->change();
            $table->enum('family_income_range', ['<1000000', '>1000000'])->nullable(false)->change();
            $table->enum('patient_relationship', ['pasien-sendiri', 'orang-tua', 'anak'])->nullable(false)->change();

            $table->unsignedInteger('tb_so_ro')->nullable(false)->change();
            $table->date('treatment_start_date')->nullable(false)->change();
            $table->date('visit_date')->nullable(false)->change();
            $table->date('information_date')->nullable(false)->change();
            $table->enum('treatment_status', ['terlaksana', 'belum'])->nullable(false)->change();
            $table->enum('nutritional_status', ['normal', 'tidak-normal'])->nullable(false)->change();
        });
    }
};
