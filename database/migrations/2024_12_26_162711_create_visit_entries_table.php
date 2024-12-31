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
        Schema::create('visit_entries', function (Blueprint $table) {
            $table->id();
    $table->time('start_time')->nullable();
    $table->time('end_time')->nullable();
    $table->unsignedBigInteger('user_id')->nullable();
    $table->unsignedBigInteger('sales_lead_management_id')->nullable();
    $table->string('starting_meter_photo')->nullable();
    $table->string('ending_meter_photo')->nullable();
    $table->string('travel_type')->nullable();
    $table->string('travel_bill')->nullable();
    $table->decimal('travel_expense', 10, 2)->nullable();
    $table->integer('starting_km')->nullable();
    $table->integer('ending_km')->nullable();
    $table->string('travel_mode')->nullable();
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_entries');
    }
};
