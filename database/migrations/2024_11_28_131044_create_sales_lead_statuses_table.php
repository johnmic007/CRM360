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
        Schema::create('sales_lead_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_lead_management_id')->nullable();
            $table->unsignedBigInteger('visit_entry_id')->nullable();
            $table->integer('potential_meet')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_book_issued')->default(false)->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('block_id')->nullable();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('visited_by')->nullable();
            $table->string('status')->nullable();
            $table->text('remarks')->nullable();
            $table->string('image')->nullable();
            $table->date('reschedule_date')->nullable();
            $table->string('contacted_person')->nullable();
            $table->string('contacted_person_designation')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->date('visited_date')->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_lead_statuses');
    }
};
