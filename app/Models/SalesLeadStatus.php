<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SalesLeadStatus extends Model
{
    protected $fillable = [
        'sales_lead_management_id', 
        'visit_entry_id',
        'potential_meet',
        'created_by',
        'is_book_issued',
        'state_id',
        'district_id',
        'block_id',
        'school_id', 
        'visited_by', 
        'status', 
        'remarks',
        'reschedule_date',
        'contacted_person', 
        'contacted_person_designation', 
        'follow_up_date', 
        'visited_date',
    ];

    public function salesLead()
    {
        return $this->belongsTo(SalesLeadManagement::class, 'school_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function issuedBooksLog()
    {
        return $this->hasMany(TestBookLog::class, 'lead_id');
    }

    public function visitedBy()
    {
        return $this->belongsTo(User::class, 'visited_by');
    }

    protected static function boot()
    {
        parent::boot();


        

        static::creating(function ($status) {
            // Fetch the status from SalesLeadManagement if sales_lead_management_id is set
            if ($status->sales_lead_management_id) {
                $salesLead = SalesLeadManagement::find($status->sales_lead_management_id);
                
                if ($salesLead) {
                    // Automatically assign the status from the SalesLeadManagement record
                    $status->status = $salesLead->status;
                    $status->school_id = $salesLead->school_id; // Ensure school_id is also set if needed
                } else {
                    throw new \Exception("SalesLeadManagement record not found for ID: {$status->sales_lead_management_id}");
                }
            }

            // Automatically assign the visited_by field to the authenticated user
            if (!$status->visited_by && Auth::check()) {
                $status->visited_by = Auth::id();
            }

            if (!$status->created_by && Auth::check()) {
                $status->created_by = Auth::id();
            }

            // Default status if SalesLeadManagement is not found or status is still empty
            if (!$status->status) {
                $status->status = 'School Nurturing';
            }
        });
    }
}
