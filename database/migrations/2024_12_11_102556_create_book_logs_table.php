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
        Schema::create('book_logs', function (Blueprint $table) {
            $table->id();
            $table->string('book_id')->nullable();
            $table->string('school_id')->nullable();
            $table->string('event')->nullable();
            $table->string('status')->default('pending')->nullable();
            $table->string('gate_pass_image')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_logs');
    }
};
