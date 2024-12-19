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
        Schema::create('wallet_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 100);
            $table->date('payment_date');
            $table->string('payment_proof')->nullable();
            $table->string('reference_number', 100)->unique();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->enum('type', ['debit', 'credit']); // Debit or credit transaction
            $table->string('description')->nullable(); // Description of the transaction
            $table->unsignedBigInteger('approved_by')->nullable(); // Who approved the transaction
            $table->timestamps();

           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_logs');
    }
};
