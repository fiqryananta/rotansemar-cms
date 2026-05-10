<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE medication_pickups MODIFY COLUMN status ENUM('menunggu', 'terrealisasi', 'pindah', 'putus_obat', 'meninggal', 'obat_terakhir') NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('medication_pickups')
                ->where('status', 'menunggu')
                ->update(['status' => null]);

            DB::statement("ALTER TABLE medication_pickups MODIFY COLUMN status ENUM('terrealisasi', 'pindah', 'putus_obat', 'meninggal', 'obat_terakhir') NULL");
        }
    }
};
