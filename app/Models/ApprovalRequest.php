<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'manager_id', 
        'message',
        'company_id',
        'user_id',
        'school_id',
        'status'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }



    protected static function boot()
    {
        parent::boot();

        static::saved(function ($approvalRequest) {
            // Check if the status is 'Approved'
            if ($approvalRequest->status === 'Approved') {
                // Create a new SalesLeadManagement record
                SalesLeadManagement::create([
                    'school_id' => $approvalRequest->school_id,
                    'allocated_to' => $approvalRequest->user_id,
                    // 'company_id' => $approvalRequest->company_id,
                    'status' => 'School Nurturing',
                ]);
            }
        });
    }


}
