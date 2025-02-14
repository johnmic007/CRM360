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

    public function closedBy()
{
    return $this->belongsTo(User::class, 'closed_by');
}


    protected static function booted()
    {
        // Automatically assign the user's company ID on creation
        static::creating(function ($shipment) {
            $shipment->company_id = Auth::user()->company_id;
        });

        // Handle status changes and update issued_books_count in SchoolBook model
        static::saving(function ($shipment) {
            // dd($shipment->status);

            if ($shipment->isDirty('status') && $shipment->status === 'delivered') {

                dd($shipment->details);
                foreach ($shipment->details as $detail) {

                    // dd($detail->quantity);
                    $schoolBook = \App\Models\SchoolBook::firstOrCreate(
                        [
                            'book_id' => $detail->book_id,
                            'school_id' => $shipment->school_id,
                        ],
                        [
                            'issued_books_count' => 0, // Default to 0 if record doesn't exist
                        ]
                    );


                    $schoolBook->issued_books_count +=$detail->quantity;

                    $schoolBook->save();

                    dd( $schoolBook->issued_books_count);


                    // Increment the issued_books_count by the quantity from the shipment
                    // $schoolBook->increment('issued_books_count', $detail->quantity);
                }
            }
        });
    }
}

