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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
    $table->string('title')->nullable();
    $table->text('description')->nullable();
    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();
    $table->unsignedBigInteger('user_id')->nullable();
    $table->string('task_type')->nullable();
    $table->unsignedBigInteger('school_id')->nullable();
    $table->unsignedBigInteger('company_id')->nullable();
    $table->string('status')->nullable();
    $table->time('time')->nullable();
    $table->unsignedBigInteger('district_id')->nullable();
    $table->unsignedBigInteger('block_id')->nullable();
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
