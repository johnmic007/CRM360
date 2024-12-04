<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'school_id',
        'event',
        'status',
        'gate_pass_image',
        'notes',
    ];
}
