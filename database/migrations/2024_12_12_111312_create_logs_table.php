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
        Schema::create('issued_books', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('book_id');
            $table->integer('count');
            $table->integer('issued_by');
            $table->timestamps();
        });

        Schema::create('test_book_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('lead_id');
            $table->integer('book_id');
            $table->integer('school_id');
            $table->enum('action', ['issued', 'returned']);
            $table->integer('count');
            $table->text('remarks')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->integer('created_by');
            $table->timestamps();
        });
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issued_books');
        Schema::dropIfExists('book_logs');

    }
};
