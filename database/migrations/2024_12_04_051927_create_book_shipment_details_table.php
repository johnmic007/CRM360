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

        Schema::create('book_shipment', function (Blueprint $table) {
            $table->id();
            $table->integer('school_id');
            $table->integer('status');
            $table->timestamps();
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
