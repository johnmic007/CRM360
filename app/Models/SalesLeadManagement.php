<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesLeadManagement extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id',
        'block_id',
        'school_id',
        'status',
        'feedback',
        'allocated_to',
        'company_id',
        'remarks',
        'contacted_person',
        'contacted_person_designation',
        'follow_up_date',
    ];

    public function allocatedUser()
    {
        return $this->belongsTo(User::class, 'allocated_to');
    }

    public function leadStatuses()
    {
        return $this->hasMany(SalesLeadStatus::class, 'sales_lead_management_id');
    }

    public function leadStatusesByStatus(array $statuses)
{
    return $this->hasMany(SalesLeadStatus::class, 'sales_lead_management_id')
                ->whereIn('status', $statuses);
}



    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class); // Adjust the foreign key if necessary
    }



    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function block()
    {
        return $this->belongsTo(Block::class);
    }


    protected static function boot()
{
    parent::boot();

    // Automatically set 'allocated_to' and 'company_id' fields before creating a new record
    static::creating(function ($model) {
        if (!auth()->user()->hasRole('admin')) {
            $model->allocated_to = auth()->id(); // Assign the current user's ID to 'allocated_to'
            $model->company_id = auth()->user()->company_id; // Assign the current user's company_id to 'company_id'
        }
    });

    // Ensure 'allocated_to' and 'company_id' fields are handled during updates
    static::updating(function ($model) {
        if (!auth()->user()->hasRole('admin')) {
            if (!$model->isDirty('allocated_to')) {
                $model->allocated_to = auth()->id(); // Reassign the current user's ID if not already updated
            }
            if (!$model->isDirty('company_id')) {
                $model->company_id = auth()->user()->company_id; // Reassign the current user's company_id if not already updated
            }
        }
    });
}

}
