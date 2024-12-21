<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClosureAmountLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'closed_by_id',
        'amount_closed',
        'closed_at',
    ];

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by_id');
    }
}
