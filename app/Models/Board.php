<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    

    // Fillable properties for mass assignment
    protected $fillable = [
        'name',        
       
    ];

    // Relationships
    public function schools()
    {
        return $this->hasMany(School::class); // A board can have many schools
    }
}
