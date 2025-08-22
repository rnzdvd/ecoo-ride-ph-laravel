<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->decimal('total_distance', 10, 2)->default(0)->after('status'); // in km
            $table->decimal('total_charged', 10, 2)->default(0)->after('total_distance'); // in PHP
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rides', function (Blueprint $table) {
            $table->dropColumn(['total_distance', 'total_charged']);
        });
    }
};
