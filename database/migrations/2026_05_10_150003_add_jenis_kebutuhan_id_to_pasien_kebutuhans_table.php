<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pasien_kebutuhans', function (Blueprint $table) {
            $table->foreignId('jenis_kebutuhan_id')->nullable()->after('pasien_id')->constrained('jenis_kebutuhans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pasien_kebutuhans', function (Blueprint $table) {
            $table->dropConstrainedForeignId('jenis_kebutuhan_id');
        });
    }
};
