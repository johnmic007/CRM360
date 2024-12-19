<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id(); // Auto-increment ID
            $table->string('name'); // School name
            $table->string('block_id'); // Block ID
            $table->string('board_id'); // Board ID
            $table->string('district_id'); // District ID
            $table->text('address')->nullable(); // School address
            $table->string('pincode', 10)->nullable(); // Pincode
            $table->string('status')->default('1'); // Active status (as string)
            $table->string('book_id')->nullable(); // Book ID
            $table->string('payment_status')->default('pending'); // Payment status
            $table->string('process_status')->default('pending'); // Process status
            $table->string('demo_date')->nullable(); // Demo date (stored as string, can be adjusted if needed)
            $table->timestamps(); // created_at and updated_at
            $table->softDeletes(); // deleted_at for soft deletes
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schools');
    }
}