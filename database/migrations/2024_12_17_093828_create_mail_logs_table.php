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
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id'); 
            $table->integer('company_id'); 
            $table->string('to_emails'); 
            $table->string('cc_emails')->nullable(); 
            $table->string('subject'); // Email Subject
            $table->text('content'); // Email Content
            $table->string('status')->default('sent'); // Status: 'sent' or 'failed'
            $table->text('error_message')->nullable(); // Error Message in case of failure
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_logs');
    }
};
