<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = ['name' , 'state_id'];



    public function blocks()
    {
        return $this->hasMany(Block::class);
    }
}
