<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class WalletPaymentLogs extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'amount', 'transaction_type', 'description' , 'payment_method' , 'payment_date',
                            'reference_number','payment_proof',

 ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
