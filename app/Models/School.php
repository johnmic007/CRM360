<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'block_id', 'address'];

    public function block()
    {
        return $this->belongsTo(Block::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
