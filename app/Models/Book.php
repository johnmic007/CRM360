<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Book extends Model
{

    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'isbn',
        'price',
        'total',
        'published_year',
        'description',
        'school_id',
    ];

    // public function schools()
    // {
    //     return $this->hasMany(School::class);
    // }

    public function schools()
{
    return $this->belongsToMany(School::class, 'school_book');
}

public function bookLogs()
{
    return $this->hasMany(BookLog::class);
}

/**
 * The issued records for this book.
 */
public function issuedBooks()
{
    return $this->hasMany(IssuedBook::class);
}


public function schoolss()
{
    return $this->belongsToMany(School::class, 'school_book')
        ->withPivot('books_count', 'issued_books_count', 'price', 'total');
}




}
