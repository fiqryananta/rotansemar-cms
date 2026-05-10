<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('opd_id')->nullable()->after('email_verified_at')->constrained('opds')->nullOnDelete();
            $table->foreignId('puskesmas_id')->nullable()->after('opd_id')->constrained('puskesmas')->nullOnDelete();
            $table->foreignId('kecamatan_id')->nullable()->after('puskesmas_id')->constrained('kecamatans')->nullOnDelete();
            $table->foreignId('kelurahan_id')->nullable()->after('kecamatan_id')->constrained('kelurahans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kelurahan_id');
            $table->dropConstrainedForeignId('kecamatan_id');
            $table->dropConstrainedForeignId('puskesmas_id');
            $table->dropConstrainedForeignId('opd_id');
        });
    }
};
