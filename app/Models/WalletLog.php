<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletLog extends Model
{
    protected $fillable = [

        'id',
        'refund_id',
        'user_id',
        'company_id',
        'trainer_visit_id',
        'amount',
        'balance',
        'is_closed',
        'payment_method',
        'payment_date',
        'payment_proof',
        'reference_number',
        'type',
        'description',
        'reimbursement_id', // New field
        'reimbursement_amount',
        'wallet_logs',
        'credit_type',
        'transaction_id',
        'approved_by'

    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function trainerVisits()
    {
        return $this->hasMany(TrainerVisit::class, 'user_id', 'user_id');
    }

public function associatedDebits()
{
    return $this->hasMany(WalletLog::class, 'wallet_logs', 'id')
        ->where('type', 'debit');
}

    

    /**
     * Scope a query to only include credit type logs.
     */
    public function scopeCredit($query)
    {
        return $query->where('type', 'credit');
    }



    public static function boot()
    {
        parent::boot();

        // Automatically set company_id before creating a wallet log
        static::creating(function ($walletLog) {
            $user = $walletLog->user;

            if ($user) {
                $walletLog->company_id = $user->company_id;
            }
        });
    }
    public function companyTransactions()
    {
        return $this->belongsToMany(CompanyTransaction::class, 'company_transaction_wallet_log')
            ->withTimestamps(); // Tracks when the association was created
    }
}
