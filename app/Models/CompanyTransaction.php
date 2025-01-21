<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyTransaction extends Model
{
    protected $fillable = [
        'id',
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
    public function generateTransactionId()
{
    $prefix = 'MGC-CA-';         // Prefix
    $month = strtoupper(date('M')); // Current month in short form

    // Combine prefix, ID, and month
    return $prefix . $this->id;
}


    public function walletLogs()
{
    return $this->belongsToMany(WalletLog::class, 'company_transaction_wallet_log')
        ->withPivot('type') // Include the type field in the pivot table
        ->withTimestamps(); // Tracks when the association was created
}

}
