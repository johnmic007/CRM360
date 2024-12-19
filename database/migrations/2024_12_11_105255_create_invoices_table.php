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
            $table->string('invoice_number');
            $table->string('file');
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('company_id');
            $table->date('issue_date')->nullable();
            $table->decimal('total', 10, 2)->default(0.00);
            $table->date('due_date')->nullable();
            $table->boolean('paid')->default(false);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('due_amount', 10, 2)->default(0.00);
            $table->string('payment_status')->default('pending');
            $table->integer('students_count')->default(0);
            $table->boolean('trainer_required')->default(false);
            $table->date('validity_start')->nullable();
            $table->date('validity_end')->nullable();
            $table->integer('books_count')->default(0);
            $table->unsignedBigInteger('closed_by')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
