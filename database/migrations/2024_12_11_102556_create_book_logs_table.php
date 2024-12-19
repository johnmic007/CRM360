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
            $table->id(); // Auto-increment ID
            $table->string('book_id'); // Book ID
            $table->string('school_id'); // School ID
            $table->string('event'); // Event description
            $table->string('status')->default('pending'); // Status
            $table->string('gate_pass_image')->nullable(); // Gate pass image path
            $table->text('notes')->nullable(); // Additional notes
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
