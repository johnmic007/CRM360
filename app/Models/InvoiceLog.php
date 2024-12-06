<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'type',
        'description',
        'payment_method',
        'reference_number',
        'paid_amount',
        'payment_date',
        'transaction_reference',
        'payment_proof',
        'school_id',
        'company_id',
    ];

    public static function boot()
    {
        parent::boot();

        // Listen to the 'creating' event
        static::creating(function ($invoiceLog) {
            // Only set school_id if the type is 'payment'

            $invoiceLog->company_id = Auth::user()->company_id;

            if ($invoiceLog->type === 'payment') {
                // Retrieve the associated school_id from the invoice using invoice_id
                $invoice = Invoice::find($invoiceLog->invoice_id);

                if ($invoice) {
                    $invoiceLog->school_id = $invoice->school_id; // Set the school_id for the log
                } else {
                    // Handle the case where the invoice is not found (optional)
                    Log::error("Invoice not found for ID: " . $invoiceLog->invoice_id);
                }
            }
        });
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
