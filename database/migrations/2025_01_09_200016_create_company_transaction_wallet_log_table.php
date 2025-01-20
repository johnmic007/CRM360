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
        Schema::create('company_transaction_wallet_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_transaction_id')
                ->constrained()
                ->onDelete('cascade'); // Ensure integrity when a transaction is deleted
            $table->foreignId('wallet_log_id')
                ->constrained()
                ->onDelete('cascade'); // Ensure integrity when a wallet log is deleted
                $table->string('type'); // Add type column (e.g., "Top-Up" or "Reimbursement")

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_transaction_wallet_log');
    }
};
