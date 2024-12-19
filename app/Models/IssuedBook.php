<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class IssuedBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'count',
        'stock_count',
        'issued_by',
    ];

    /**
     * The user to whom the books are issued.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    

    /**
     * The book that has been issued.
     */
    // public function book()
    // {
    //     return $this->belongsTo(Book::class);
    // }


    public function bookLogs()
    {
        return $this->hasMany(TestBookLog::class, 'lead_id');
    }


    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * The user who issued the books (sales team).
     */
    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Automatically set issued_by to the authenticated user's ID and process the issued/returned books.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically set issued_by when creating
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->issued_by = Auth::id();
            }

        });

        
    }

}
