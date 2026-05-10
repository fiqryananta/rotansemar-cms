<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visit_results', function (Blueprint $table) {
            $table->text('location_detail')->nullable()->after('follow_up');
        });
    }

    public function down(): void
    {
        Schema::table('visit_results', function (Blueprint $table) {
            $table->dropColumn('location_detail');
        });
    }
};