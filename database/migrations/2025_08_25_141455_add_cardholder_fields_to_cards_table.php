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
        Schema::table('cards', function (Blueprint $table) {
            $table->string('cardholder_first_name')->nullable()->after('id');
            $table->string('cardholder_last_name')->nullable()->after('cardholder_first_name');
            $table->string('cardholder_email')->nullable()->after('cardholder_last_name');
            $table->string('cardholder_phone_number')->nullable()->after('cardholder_email');
            $table->string('network')->nullable()->after('cardholder_phone_number'); // e.g. VISA, MasterCard
            $table->enum('type', ['DEBIT', 'CREDIT'])->nullable()->after('network');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn([
                'cardholder_first_name',
                'cardholder_last_name',
                'cardholder_email',
                'cardholder_phone_number',
                'network',
                'type',
            ]);
        });
    }
};
