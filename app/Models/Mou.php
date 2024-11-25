<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mou extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',         
        'description',   
        'school_id',    
        'company_id',    
        'image',         
    ];

    /**
     * Define the relationship with the School model.
     */
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    /**
     * Define the relationship with the Company model.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
