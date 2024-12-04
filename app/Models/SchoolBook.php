<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolBook extends Model
{
    use HasFactory;

    protected $table = 'school_book'; // Set the correct table name
    public $timestamps = true; // Enable timestamps if the table includes `created_at` and `updated_at`

    protected $fillable = [
        'book_id',
        'invoice_id',
        'school_id',
        'books_count',
        'price',
        'total',
        'issued_books_count',
    ];

    /**
     * Relationships
     */

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /**
     * Boot method for automatic setting of school_id.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set school_id based on the invoice_id
        static::creating(function ($schoolBook) {
            $schoolBook->school_id = $schoolBook->invoice->school_id;
        });

        static::updating(function ($schoolBook) {
            // Ensure school_id is updated if invoice_id changes
            if ($schoolBook->isDirty('invoice_id')) {
                $schoolBook->school_id = $schoolBook->invoice->school_id;
            }
        });
    }
}
