<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('user_id')
                ->constrained()
                ->onDelete('cascade'); // Reference to users table
            $table->string('payment_method_id', 100); // Xendit payment method ID / token
            $table->string('brand', 20); // Visa, Mastercard, Amex, JCB
            $table->char('last_4', 4); // Last 4 digits
            $table->string('expiry_date', 5); // MM/YY
            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
