<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolUser extends Model
{
    // Define the associated table name
    protected $table = 'school_user';

    // Specify the fillable fields
    protected $fillable = [
        'id',
        'school_id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
