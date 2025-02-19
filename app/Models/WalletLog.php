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

    public function trainerVisit()
    {
    return $this->belongsTo(TrainerVisit::class, 'trainer_visit_id', 'id');
    }

    // Indirect relationship with School through TrainerVisit
    public function school()
    {
        return $this->hasOneThrough(
            School::class,
            TrainerVisit::class,
            'id',        // Foreign key on TrainerVisit table
            'id',        // Foreign key on School table
            'trainer_visit_id', // Local key on WalletLog table
            'school_id'  // Local key on TrainerVisit table
        );
    }

    // Indirect relationship with District through TrainerVisit → School
    public function district()
    {
        return $this->hasOneThrough(
            District::class,
            School::class,
            'id',        // Foreign key on School table
            'id',        // Foreign key on District table
            'school_id', // Local key on TrainerVisit table
            'district_id'// Local key on School table
        );
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
