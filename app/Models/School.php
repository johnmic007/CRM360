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
        'board_id',
        'state_id',
        'district_id',
        'address',
        'pincode',
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

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
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

    public function bookShipments()
{
    return $this->hasMany(BookShipment::class);
}



public function schoolBook()
{
    return $this->hasMany(SchoolBook::class);
}


    public function books()
    {
        return $this->belongsToMany(Book::class, 'school_book');
    }


    // public function books()
    // {
    //     return $this->hasMany(Book::class); // Adjust this if it's a belongsToMany relationship
    // }


    public function mou()
    {
        return $this->hasMany(Invoice::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }





}
