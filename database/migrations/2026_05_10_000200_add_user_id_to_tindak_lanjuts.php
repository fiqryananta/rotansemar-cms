<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pasien_kebutuhan_tindak_lanjuts')) {
            return;
        }

        if (Schema::hasColumn('pasien_kebutuhan_tindak_lanjuts', 'user_id')) {
            return;
        }

        Schema::table('pasien_kebutuhan_tindak_lanjuts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('pasien_kebutuhan_tindak_lanjuts')) {
            return;
        }

        if (!Schema::hasColumn('pasien_kebutuhan_tindak_lanjuts', 'user_id')) {
            return;
        }

        Schema::table('pasien_kebutuhan_tindak_lanjuts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
