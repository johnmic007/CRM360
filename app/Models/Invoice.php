<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'school_id',
        'company_id',
        'issue_date',
        'total',    
        'due_date',
        'paid',
        'total_amount',
        'status',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function logs()
    {
        return $this->hasMany(InvoiceLog::class);
    }


    protected static function boot()
{
    parent::boot();

    static::created(function ($invoice) {
        $invoice->logs()->create([
            'type' => 'created',
            'description' => 'Invoice created with number ' . $invoice->invoice_number,
        ]);
    });

    static::updated(function ($invoice) {
        $invoice->logs()->create([
            'type' => 'edited',
            'description' => 'Invoice updated with number ' . $invoice->invoice_number,
        ]);
    });

    static::deleted(function ($invoice) {
        $invoice->logs()->create([
            'type' => 'deleted',
            'description' => 'Invoice deleted with number ' . $invoice->invoice_number,
        ]);
    });
}

}
