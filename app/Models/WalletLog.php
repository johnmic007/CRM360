<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletLog extends Model
{
    protected $fillable = [
        
        'user_id', 
        'company_id',
        'amount',
        'payment_method' , 
        'payment_date',
        'payment_proof',
        'reference_number',
        'type', 
        'description', 
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


    
}
