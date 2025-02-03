<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DraftMou extends Model
{
    use HasFactory;

    protected $table = 'draft_mous'; // Ensure your table name matches your migration

    /** 🛠 Fillable Properties */
    protected $fillable = [
        'date',
        'school_name',
        'school_address',
        'services',
        'items_id',
        'state_id',
        'district_id',
        'block_id',
        'school_id',
        'academic_year_start',
        'academic_year_end',
        'course_duration_end',
        'classes', // Stored as JSON
        'advance_payment',
        'mid_payment',
        'final_payment',
        'payment_type',
        'payment_value',
        'mode_of_payment',
        'due_days',
        'dispute_resolution',
        'company_city',
        'company_state',
    ];

    /** 🎯 Casts */
    protected $casts = [
        'date' => 'date',
        'academic_year_start' => 'date',
        'academic_year_end' => 'date',
        'course_duration_end' => 'date',
        'classes' => 'array', // JSON array for class-wise data
    ];

    /** 🏫 Get total students */
    protected function totalStudents(): Attribute
    {
        return Attribute::get(fn () => collect($this->classes)->sum('no_of_students'));
    }

    /** 💰 Get total revenue */
    protected function totalRevenue(): Attribute
    {
        return Attribute::get(fn () => collect($this->classes)->sum(fn ($class) => 
            ($class['no_of_students'] ?? 0) * ($class['cost_per_student'] ?? 0)
        ));
    }
}
