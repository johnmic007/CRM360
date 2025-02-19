<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('internal_wallet_transfers', function (Blueprint $table) {
            $table->decimal('from_balance', 15, 2)->after('from_user_id')->nullable()->comment('Balance of sender before transfer');
            $table->decimal('to_balance', 15, 2)->after('to_user_id')->nullable()->comment('Balance of receiver before transfer');
        });
    }
    public function up(): void
    {
        Schema::create('internal_wallet_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_user_id')->constrained('users')->onDelete('cascade'); // Sender ID
            $table->foreignId('to_user_id')->constrained('users')->onDelete('cascade'); // Receiver ID
            $table->decimal('amount', 15, 2); // Transfer amount
            $table->enum('approval_status', ['Pending', 'Approved', 'Rejected'])->default('Pending'); // Approval status
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null'); // Approver ID
            $table->timestamp('approved_at')->nullable(); // Approval timestamp
            $table->foreignId('from_transaction_id')->nullable()->constrained('wallet_logs')->onDelete('set null'); // Sender transaction ID
            $table->foreignId('to_transaction_id')->nullable()->constrained('wallet_logs')->onDelete('set null'); // Receiver transaction ID
            $table->text('remarks')->nullable(); // Optional comments
            $table->foreignId('request_by')->constrained('users')->onDelete('cascade'); // Requestor ID
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade'); // Company ID
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internal_wallet_transfers', function (Blueprint $table) {
            $table->dropColumn(['from_balance', 'to_balance']);
        });
    }
};
