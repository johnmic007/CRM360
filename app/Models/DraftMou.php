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
        'created_by',
        'installments',
        'agreement_period',
        'academic_year_start',
        'academic_year_end',
        'course_duration_end',
        'classes', // Stored as JSON
        'installments_count',
        'payment_type',
        'payment_value',
        'mode_of_payment',
        'due_days',
        'dispute_resolution',
        'company_city',
        'company_state',
        'item_remarks',
    ];


    /** 🎯 Casts */protected $casts = [
    'date' => 'date',
    'academic_year_start' => 'date',
    'academic_year_end' => 'date',
    'course_duration_end' => 'date',
    'classes' => 'array', // Ensure classes are stored as JSON
    'installments' => 'array', // ✅ Convert installments to array
];

// Ensure installments is always an array to prevent errors
public function getInstallmentsAttribute($value)
{
    return $value ? json_decode($value, true) : [];
}

    /** 🏫 Get total students */
    protected function totalStudents(): Attribute
    {
        return Attribute::get(fn () => collect($this->classes)->sum('no_of_students'));
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /** 💰 Get total revenue */
    protected function totalRevenue(): Attribute
    {
        return Attribute::get(fn () => collect($this->classes)->sum(fn ($class) =>
            ($class['no_of_students'] ?? 0) * ($class['cost_per_student'] ?? 0)
        ));
    }
}
