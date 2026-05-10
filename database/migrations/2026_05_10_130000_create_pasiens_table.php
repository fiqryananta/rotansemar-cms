<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasiens', function (Blueprint $table) {
            $table->id();

            // Data Diri
            $table->string('name');
            $table->string('nik', 16)->unique();
            $table->date('birth_date');
            $table->enum('gender', ['laki-laki', 'perempuan']);
            $table->foreignId('faskes_id')->constrained('faskes')->cascadeOnDelete();
            $table->foreignId('puskesmas_id')->constrained('puskesmas')->cascadeOnDelete();
            $table->foreignId('kecamatan_id')->constrained('kecamatans')->cascadeOnDelete();
            $table->foreignId('kelurahan_id')->constrained('kelurahans')->cascadeOnDelete();
            $table->text('address');
            $table->string('coordinates');
            $table->text('new_address')->nullable();
            $table->unsignedInteger('weight')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->boolean('ever_received_assistance')->default(false);
            $table->boolean('willing_to_help')->default(false);

            // Pekerjaan
            $table->enum('economic_status', ['miskin', 'sederhana', 'mampu']);
            $table->foreignId('pekerjaan_id')->nullable()->constrained('pekerjaan')->nullOnDelete();
            $table->string('workplace_name')->nullable();
            $table->string('workplace_address')->nullable();

            // Keluarga
            $table->string('family_head_name');
            $table->string('family_head_nik', 16);
            $table->foreignId('family_head_pekerjaan_id')->nullable()->constrained('pekerjaan')->nullOnDelete();
            $table->enum('parent_marital_status', ['menikah', 'cerai'])->nullable();
            $table->string('parenting_pattern')->nullable();
            $table->enum('family_income_range', ['<1000000', '>1000000']);
            $table->boolean('respondent')->default(false);
            $table->enum('patient_relationship', ['pasien-sendiri', 'orang-tua', 'anak']);

            // Riwayat Kesehatan
            $table->unsignedInteger('tb_so_ro');
            $table->date('treatment_start_date');
            $table->date('visit_date');
            $table->date('information_date');
            $table->enum('treatment_status', ['terlaksana', 'belum']);
            $table->string('transmission_source')->nullable();
            $table->text('follow_up_plan')->nullable();
            $table->boolean('pregnancy_status')->default(false);
            $table->boolean('comorbid_status')->default(false);
            $table->boolean('smoking_behavior')->default(false);
            $table->boolean('family_smoking_status')->default(false);
            $table->enum('immunization_status', ['lengkap', 'tidak-lengkap'])->nullable();
            $table->enum('nutritional_status', ['normal', 'tidak-normal']);
            $table->boolean('jkn_ownership')->default(false);

            // Kondisi Rumah
            $table->string('home_area')->nullable();
            $table->string('house_area')->nullable();
            $table->string('house_type')->nullable();
            $table->string('house_status')->nullable();
            $table->string('home_lighting')->nullable();
            $table->string('home_humidity')->nullable();
            $table->string('home_cleanliness')->nullable();
            $table->string('home_floor')->nullable();
            $table->string('home_ventilation')->nullable();
            $table->string('home_ceiling')->nullable();
            $table->string('home_ceiling_condition')->nullable();
            $table->string('home_wall')->nullable();
            $table->string('home_bedroom_window')->nullable();
            $table->string('home_family_room_window')->nullable();
            $table->string('home_kitchen_smoke_hole')->nullable();
            $table->string('home_open_family_room_window')->nullable();
            $table->string('home_clean_house_habit')->nullable();

            // Kondisi Sanitasi
            $table->string('sanitation_clean_water')->nullable();
            $table->string('sanitation_toilet')->nullable();
            $table->string('sanitation_wastewater_disposal')->nullable();
            $table->string('sanitation_garbage_water_disposal')->nullable();
            $table->string('sanitation_trash')->nullable();
            $table->string('sanitation_feces_disposal')->nullable();
            $table->string('sanitation_throw_trash_habit')->nullable();
            $table->string('sanitation_handwashing_habit')->nullable();

            // Kondisi Hewan
            $table->boolean('has_livestock')->nullable();
            $table->boolean('has_animal_cage')->nullable();

            $table->timestamps();

            $table->index(['name', 'nik']);
            $table->index('treatment_start_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasiens');
    }
};
