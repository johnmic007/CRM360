<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'block_id',
        'address',
        'status',
        'book_id',
        'payment_status',
        'process_status',
        'demo_date',

    ];

    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    public function schoolPayments()
    {
        return $this->hasMany(InvoiceLog::class );
    }

    public function leadStatuses()
    {
        return $this->hasMany(SalesLeadStatus::class, 'school_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function books()
    {
        return $this->belongsToMany(Book::class, 'school_book');
    }


    // public function books()
    // {
    //     return $this->hasMany(Book::class); // Adjust this if it's a belongsToMany relationship
    // }


    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
