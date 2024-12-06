<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class BookShipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id', 
        'company_id',
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

    protected static function booted()
    {
        static::creating(function ($shipment) {
            // Assign the authenticated user's company_id
            $shipment->company_id = Auth::user()->company_id;
        });
    }
}

