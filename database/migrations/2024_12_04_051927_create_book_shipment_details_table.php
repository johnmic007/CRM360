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
        Schema::create('book_shipment_details', function (Blueprint $table) {
            $table->id();
            $table->integer('book_shipment_id');
            $table->integer('book_id');
            $table->integer('quantity');
            $table->timestamps();
        });

        Schema::create('book_shipments', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('school_id'); 
            $table->unsignedBigInteger('company_id'); 
            $table->string('status')->default('pending'); 
            $table->unsignedBigInteger('district_id')->nullable(); 
            $table->unsignedBigInteger('block_id')->nullable(); 
            $table->string('mode_of_transport')->nullable(); 
            $table->unsignedBigInteger('closed_by')->nullable(); 
            $table->string('tracking_number')->nullable(); 
            $table->string('bills_and_gatepass')->nullable(); 
            $table->text('remarks')->nullable(); 
            $table->timestamps(); 
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_shipment_details');
    }
};
