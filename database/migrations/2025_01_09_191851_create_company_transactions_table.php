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
        Schema::create('company_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique(); // Unique transaction ID
            $table->unsignedBigInteger('performed_by'); // User ID of the company role performing the action
            $table->unsignedBigInteger('wallet_user_id')->nullable(); // User ID of the wallet being topped up (nullable if not applicable)
            $table->decimal('amount', 10, 2); // Transaction amount
            $table->enum('type', ['credit', 'debit']); // Transaction type
            $table->text('description')->nullable(); // Description of the transaction
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_transactions');
    }
};
