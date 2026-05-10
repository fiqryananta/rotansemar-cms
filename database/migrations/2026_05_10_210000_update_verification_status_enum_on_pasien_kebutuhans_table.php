<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pasien_kebutuhans')
            ->where('verification_status', 'tidak-layak')
            ->update(['verification_status' => 'pending']);

        DB::statement("ALTER TABLE pasien_kebutuhans MODIFY verification_status ENUM('pending', 'proses', 'pending_bantuan', 'tidak_layak', 'selesai') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::table('pasien_kebutuhans')
            ->whereIn('verification_status', ['pending_bantuan', 'selesai'])
            ->update(['verification_status' => 'pending']);

        DB::table('pasien_kebutuhans')
            ->where('verification_status', 'tidak_layak')
            ->update(['verification_status' => 'pending']);

        DB::statement("ALTER TABLE pasien_kebutuhans MODIFY verification_status ENUM('proses', 'pending', 'tidak-layak') NOT NULL DEFAULT 'pending'");
    }
};
