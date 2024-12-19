<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_lead_management', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('district_id');
            $table->unsignedBigInteger('block_id');
            $table->unsignedBigInteger('state_id');
            $table->unsignedBigInteger('school_id');
            $table->string('status')->default('pending');
            $table->text('feedback')->nullable();
            $table->unsignedBigInteger('allocated_to')->nullable();
            $table->unsignedBigInteger('company_id');
            $table->text('remarks')->nullable();
            $table->string('contacted_person')->nullable();
            $table->string('contacted_person_designation')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Created at and Updated at timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_lead_management');
    }
};

