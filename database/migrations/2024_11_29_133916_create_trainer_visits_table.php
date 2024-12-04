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
            $table->integer('user_id');
            $table->integer('school_id');
            $table->date('visit_date');
            $table->string('travel_mode');  // 'car' or 'bike'
            $table->decimal('distance_traveled', 8, 2);  // Distance in km
            $table->decimal('travel_expense', 8, 2)->default(0);  // Calculated travel expense
            $table->decimal('food_expense', 8, 2)->default(0);  // Food expense
            $table->decimal('total_expense', 8, 2)->default(0);  // Total expense (travel + food)
            $table->string('gps_photo')->nullable();  // GPS photo file path
            $table->string('travel_bill')->nullable();  // Bill photo file path
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
