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
        Schema::create('trainer_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->date('visit_date');
            $table->string('travel_mode', 100)->nullable();
            $table->string('description')->nullable();
            $table->string('travel_type', 100)->nullable();


            $table->string('starting_meter_photo')->nullable();
            $table->decimal('starting_km', 10, 2)->nullable();
            $table->string('ending_meter_photo')->nullable();
            $table->decimal('ending_km', 10, 2)->nullable();
            $table->decimal('distance_traveled', 10, 2)->nullable();
            $table->decimal('travel_expense', 10, 2)->nullable();
            $table->decimal('food_expense', 10, 2)->nullable();
            $table->decimal('total_expense', 10, 2)->nullable();
            $table->string('gps_photo')->nullable();
            $table->string('travel_bill')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('sales_role_evaluation', ['approved', 'rejected' , 'pending'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainer_visits');
    }
};
