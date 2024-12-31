<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('block_id')->nullable();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->date('issue_date')->nullable();
            $table->text('files')->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->date('due_date')->nullable();
            $table->boolean('paid')->default(false)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->decimal('due_amount', 10, 2)->nullable();
            $table->string('payment_status')->nullable();
            $table->integer('students_count')->nullable();
            $table->boolean('trainer_required')->default(false)->nullable();
            $table->date('validity_start')->nullable();
            $table->date('validity_end')->nullable();
            $table->integer('books_count')->nullable();
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
