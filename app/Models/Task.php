<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'user_id',
        'task_type',
        'school_id',
        'company_id',
        'status',
        'task_type',
        'time',
        'district_id',
        'block_id',
    ];

    // Optional: Ensure the task belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($task) {
            if (Auth::check()) {
                $task->company_id = Auth::user()->company_id;
            }
        });
    }
}
