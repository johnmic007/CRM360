<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesLeadManagement extends Model
{
    use HasFactory;
    protected $fillable = ['district', 'block', 'school', 'status', 'feedback'];
}
