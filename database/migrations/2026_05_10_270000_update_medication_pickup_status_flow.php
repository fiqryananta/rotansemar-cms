<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medication_pickups', function (Blueprint $table) {
            if (!Schema::hasColumn('medication_pickups', 'transfer_date')) {
                $table->date('transfer_date')->nullable()->after('actual_date');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::table('medication_pickups')->where('status', 'terrealisasi_obat_terakhir')->update(['status' => 'obat_terakhir']);
            DB::table('medication_pickups')->where('status', 'tidak_datang')->update(['status' => 'putus_obat']);
            DB::table('medication_pickups')->where('status', 'pindah_fasyankes')->update(['status' => 'pindah']);
            DB::statement("ALTER TABLE medication_pickups MODIFY COLUMN status ENUM('terrealisasi', 'pindah', 'putus_obat', 'meninggal', 'obat_terakhir') NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('medication_pickups')->where('status', 'obat_terakhir')->update(['status' => 'terrealisasi_obat_terakhir']);
            DB::table('medication_pickups')->where('status', 'putus_obat')->update(['status' => 'tidak_datang']);
            DB::table('medication_pickups')->where('status', 'pindah')->update(['status' => 'pindah_fasyankes']);
            DB::statement("ALTER TABLE medication_pickups MODIFY COLUMN status ENUM('terrealisasi', 'terrealisasi_obat_terakhir', 'tidak_datang', 'meninggal', 'pindah_fasyankes') NULL");
        }

        Schema::table('medication_pickups', function (Blueprint $table) {
            if (Schema::hasColumn('medication_pickups', 'transfer_date')) {
                $table->dropColumn('transfer_date');
            }
        });
    }
};
