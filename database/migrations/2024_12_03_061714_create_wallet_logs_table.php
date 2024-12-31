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
    $table->unsignedBigInteger('user_id')->nullable();
    $table->unsignedBigInteger('company_id')->nullable();
    $table->decimal('amount', 10, 2)->nullable();
    $table->string('payment_method')->nullable();
    $table->timestamp('payment_date')->nullable();
    $table->string('payment_proof')->nullable();
    $table->string('reference_number')->nullable();
    $table->string('type')->nullable();
    $table->text('description')->nullable();
    $table->unsignedBigInteger('approved_by')->nullable();
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
