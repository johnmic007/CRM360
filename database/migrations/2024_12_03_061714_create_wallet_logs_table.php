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
            $table->unsignedBigInteger('user_id'); // Related user
            $table->decimal('amount', 10, 2); // Amount of the transaction
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
