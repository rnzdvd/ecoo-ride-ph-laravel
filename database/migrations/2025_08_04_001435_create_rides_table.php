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
        Schema::create('rides', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id'); // local user
            $table->string('scooter_id'); // external ID from API

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->timestamp('last_billed_at')->nullable();
            $table->integer('billed_intervals')->default(0);

            $table->string('status')->default('active'); // active, completed, auto_ended

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
