<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookShipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id', 
        'status',
        'district_id',
        'block_id',
        'mode_of_transport',
        'closed_by',
        'tracking_number',
        'bills_and_gatepass',
        'remarks',
    
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function details()
    {
        return $this->hasMany(BookShipmentDetail::class);
    }
}

