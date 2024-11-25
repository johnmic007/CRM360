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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // Unique invoice number
            $table->foreignId('school_id')->constrained()->cascadeOnDelete(); // Link to School
            $table->foreignId('company_id')->constrained()->cascadeOnDelete(); // Link to Company
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('draft'); // Status: draft, sent, paid, etc.
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
