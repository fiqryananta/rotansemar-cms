<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medication_pickups', function (Blueprint $table) {
            if (Schema::hasColumn('medication_pickups', 'faskes_id')) {
                $table->foreignId('faskes_id')->nullable()->change();
            }

            if (!Schema::hasColumn('medication_pickups', 'puskesmas_id')) {
                $table->foreignId('puskesmas_id')->nullable()->after('faskes_id')->constrained('puskesmas')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('medication_pickups', function (Blueprint $table) {
            if (Schema::hasColumn('medication_pickups', 'puskesmas_id')) {
                $table->dropForeignKeyIfExists(['puskesmas_id']);
                $table->dropColumn('puskesmas_id');
            }

            if (Schema::hasColumn('medication_pickups', 'faskes_id')) {
                $table->foreignId('faskes_id')->nullable(false)->change();
            }
        });
    }
};
