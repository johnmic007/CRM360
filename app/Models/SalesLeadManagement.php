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
        'state_id ',
        'school_id',
        'status',
        'allocated_to',
        'company_id',
       
    ];

    public function allocatedUser()
    {
        return $this->belongsTo(User::class, 'allocated_to');
    }

    public function leadStatuses()
    {
        return $this->hasMany(SalesLeadStatus::class, 'sales_lead_management_id');
    }

    // public function issuedBooksLog()
    // {
    //     return $this->hasMany(BookLog::class, 'lead_id');
    // }


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


        static::saved(function ($model) {
            // Define an array of statuses and their corresponding actions
            $statuses = [
                'School Nurturing' => 'School Nurturing',
                'Demo reschedule'  => 'Demo reschedule',
                'Demo Completed'   => 'Demo Completed',
                'deal_won'         => 'Deal Won',
                'deal_lost'        => 'Deal Lost',
                'support'   => 'Support',

            ];
        
            if (!\App\Models\SalesLeadStatus::where('sales_lead_management_id', $model->id)
            ->where('status', $statuses[$model->status])
            ->exists()) {
           \App\Models\SalesLeadStatus::create([
               'sales_lead_management_id' => $model->id,
               'status' => $statuses[$model->status],
               'visited_by' => auth()->id(),
           ]);
       }
       
        });
        

        // Automatically set 'allocated_to' and 'company_id' fields before creating a new record
        static::creating(function ($model) {



            if ($model->school_id) {
                $school = \App\Models\School::find($model->school_id); // Assuming there's a School model
                if ($school) {
                    $model->block_id = $model->block_id ?? $school->block_id;
                    $model->district_id = $model->district_id ?? $school->district_id;
                    $model->state_id = $model->state_id ?? $school->state_id;
                }
            }



            if (!auth()->user()->hasRole('admin')) {
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
