<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Items extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'price',
        'remarks',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) { // Change variable name to match the model
            if (Auth::check()) {
                $item->company_id = Auth::user()->company_id; // Set the user's company_id
            }
        });
    }
}
