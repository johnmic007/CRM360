<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('block_id')->constrained('blocks')->onDelete('cascade'); // Foreign key to blocks table
            $table->string('name'); // School name
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};

