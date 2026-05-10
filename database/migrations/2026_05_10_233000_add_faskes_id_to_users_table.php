<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'faskes_id')) {
                $table->foreignId('faskes_id')
                    ->nullable()
                    ->after('opd_id')
                    ->constrained('faskes')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'faskes_id')) {
                $table->dropConstrainedForeignId('faskes_id');
            }
        });
    }
};
