<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blocks', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade'); // Foreign key to districts table
            $table->string('name'); // Block name
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
