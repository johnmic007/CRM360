<?php

namespace App\Models;

use App\Models\Scopes\CompanyScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    protected static function booted()
    {
        static::addGlobalScope(new CompanyScope());
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($mou) {
            if (Auth::check()) {
                $mou->company_id = Auth::user()->company_id;
            }
        });
    }
}
