<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyTransaction extends Model
{
    protected $fillable = [
        'transaction_id', // Unique transaction ID
        'amount',
        'balance',
        'type',           // e.g., 'credit' or 'debit'
        'performed_by',   // User ID of the company role performing the action
        'requested_at',
        'issued_at',
        'wallet_user_id', // User ID of the wallet being topped up (if applicable)
        'description',    // Description of the transaction
    ];

    /**
     * Generate a unique transaction ID.
     */
    public static function generateTransactionId($amount)
    {
        $prefix = '1'; // Starting number prefix
        $month = strtoupper(date('M')); // Current month in short form
        $date = date('d');             // Current date
        $amountFormatted = str_pad((int)$amount, 4, '0', STR_PAD_LEFT); // Format amount (4 digits)
        
        return $prefix . $month . $date . $amountFormatted;
    }


    public function walletLogs()
{
    return $this->belongsToMany(WalletLog::class, 'company_transaction_wallet_log')
        ->withPivot('type') // Include the type field in the pivot table
        ->withTimestamps(); // Tracks when the association was created
}

}
