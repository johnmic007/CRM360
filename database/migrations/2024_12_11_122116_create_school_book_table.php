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
        Schema::create('school_book', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('school_id');
            $table->string('payment_status');
            $table->integer('books_count')->default(0);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->integer('issued_books_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_book');
    }
};
