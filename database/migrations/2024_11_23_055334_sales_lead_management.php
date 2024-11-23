<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_lead_management', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('district'); // Name of the district
            $table->string('block'); // Name of the block
            $table->string('school'); // Name of the school
            $table->enum('status', ['new', 'active', 'rejected', 'converted'])->default('converted'); // Status of the lead
            $table->text('feedback')->nullable(); // Feedback (nullable field)
            $table->timestamps(); // Created at and Updated at timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_lead_management');
    }
};

