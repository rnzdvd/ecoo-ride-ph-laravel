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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 50);
            $table->enum('status', ['pending', 'succeeded', 'failed', 'canceled', 'refunded'])->default('succeeded');
            $table->string('reference_id', 100)->nullable();
            $table->string('failure_reason', 255)->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->unique('reference_id');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
