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
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();

            $table->unsignedBigInteger('company_id')->nullable();
            $table->date('visit_date')->nullable();
            $table->string('travel_mode')->nullable();
            $table->string('starting_meter_photo')->nullable();
            $table->integer('starting_km')->nullable();
            $table->string('ending_meter_photo')->nullable();
            $table->integer('ending_km')->nullable();
            $table->integer('distance_traveled')->nullable();
            $table->decimal('travel_expense', 10, 2)->nullable();
            $table->decimal('food_expense', 10, 2)->nullable();
            $table->decimal('total_expense', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('gps_photo')->nullable();
            $table->string('travel_bill')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('approval_status')->nullable();
            $table->unsignedBigInteger('verify_by')->nullable();
            $table->string('verify_status')->nullable();
            $table->text('clarification_question')->nullable();
            $table->text('clarification_answer')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('sales_role_evaluation')->nullable();
            $table->string('travel_type')->nullable();
            $table->string('files')->nullable();
            $table->unsignedBigInteger('visit_entry_id')->nullable();
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
