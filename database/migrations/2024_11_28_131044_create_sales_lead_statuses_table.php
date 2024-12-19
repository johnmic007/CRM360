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
            $table->unsignedBigInteger('sales_lead_management_id');
            $table->boolean('potential_meet')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('visited_by')->nullable();
            $table->string('status')->default('pending');
            $table->text('remarks')->nullable();
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
