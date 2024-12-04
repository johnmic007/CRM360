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
        Schema::create('wallet_payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relate to users table
            $table->decimal('amount', 10, 2); // Store the top-up amount
            $table->string('transaction_type'); // e.g., 'topup'
            $table->string('description')->nullable(); // Description of the transaction
            $table->string('payment_method'); // Payment method (cash, bank_transfer, etc.)
            $table->string('reference_number')->nullable(); // Optional reference number for payment
            $table->string('payment_proof')->nullable(); // Path to payment proof file (optional)
            $table->date('payment_date'); // Date of the payment
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_payment_logs');
    }
};
