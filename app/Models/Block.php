<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'district_id'];

    public function district()
    {
        return $this->belongsTo(District::class);
    }


    public function schools()
    {
        return $this->hasMany(School::class);
    }
}
