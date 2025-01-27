<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class SalesLeadStatus extends Model
{
    protected $fillable = [
        'sales_lead_management_id',
        'visit_entry_id',
        'potential_meet',
        'created_by',
        'is_book_issued',
        'skip_contact',
        'contact_number',
        'state_id',
        'district_id',
        'block_id',
        'school_id',
        'visited_by',
        'status',
        'remarks',
        'image',
        'reschedule_date',
        'contacted_person',
        'contacted_person_designation',
        'follow_up_date',
        'visited_date',
        'message',
    ];

    public function salesLead()
    {
        return $this->belongsTo(SalesLeadManagement::class, 'school_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function block()
    {
        return $this->belongsTo(Block::class);
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
    
        static::saving(function ($status) {
            // Log the incoming data
            Log::info('Saving triggered with status:', ['status' => $status->toArray()]);
    
            $data = $status;

            // dd($data);




            if ($status->status === 'Assigned to Another User') {
                Log::warning('Attempt to save status "Assigned to Another User". Not creating entry.', [
                    'school_id' => $status->school_id,
                    'user_id' => Auth::id(),
                ]);

                ApprovalRequest::create([
                    'message'    => $status->message ?? 'Request for reassignment.',
                    'company_id' => Auth::user()->company_id ?? null,
                    'user_id'    => Auth::id(),
                    'school_id'  => $status->school_id,
                    'status'     => 'Pending',
                ]);
    
                // Send a Filament notification
                \Filament\Notifications\Notification::make()
                    ->title('School Already Assigned')
                    ->body('This school is assigned to another user. Please contact your manager for reassignment.')
                    ->warning()
                    ->send();
    
                // Stop further saving
                return false;
            }
            
    
            // Check if a SalesLeadManagement record exists for the given school and user
            $existing = SalesLeadManagement::where('school_id', $data['school_id'] ?? null)
                ->where('allocated_to', $data['allocated_to'] ?? Auth::id())
                ->first();
    
            Log::info('Existing SalesLeadManagement record:', ['existing' => $existing]);
    
            if (!$existing) {
                // Create a new SalesLeadManagement record
                $salesLeadManagement = SalesLeadManagement::create([
                    'school_id'    => $data['school_id'] ?? null,
                    'district_id'  => $data['district_id'] ?? null,
                    'block_id'     => $data['block_id'] ?? null,
                    'state_id'     => $data['state_id'] ?? null,
                    'status'       => $data['status'] ?? 'School Nurturing',
                    'allocated_to' => $data['allocated_to'] ?? Auth::id(),
                    'company_id'   => Auth::user()->company_id ?? null,
                ]);
    
                Log::info('New SalesLeadManagement created:', ['salesLeadManagement' => $salesLeadManagement->toArray()]);
    
                // Check if the user is already assigned to the school
                $alreadyAssigned = SchoolUser::where('school_id', $data['school_id'] ?? null)
                    ->where('user_id', Auth::id())
                    ->exists();
    
                Log::info('User already assigned to school:', ['alreadyAssigned' => $alreadyAssigned]);
    
                if (!$alreadyAssigned) {
                    // Assign the user to the school
                    SchoolUser::create([
                        'school_id' => $data['school_id'],
                        'user_id'   => Auth::id(),
                    ]);
    
                    Log::info('User assigned to school:', [
                        'school_id' => $data['school_id'],
                        'user_id'   => Auth::id(),
                    ]);
                }
    
                // Update the model with the new SalesLeadManagement ID and default status
                $status->sales_lead_management_id = $salesLeadManagement->id;
                $status->status = 'School Nurturing';
            } else {
                // Reference the existing record
                $status->sales_lead_management_id = $existing->id;
    
                if (!$status->status) {
                    $status->status = $existing->status ?? 'School Nurturing';
                }
    
                Log::info('Using existing SalesLeadManagement record:', [
                    'sales_lead_management_id' => $existing->id,
                    'status' => $status->status,
                ]);
            }
    
            // Sync the status with SalesLeadManagement
            if ($status->sales_lead_management_id) {
                $salesLead = SalesLeadManagement::find($status->sales_lead_management_id);
    
                if ($salesLead) {
                    // Update the status in the SalesLeadManagement record
                    $salesLead->status = $status->status;
                    $salesLead->save();
    
                    Log::info('Updated SalesLeadManagement status:', [
                        'id' => $salesLead->id,
                        'status' => $salesLead->status,
                    ]);
                } else {
                    Log::error('SalesLeadManagement record not found for ID:', [
                        'id' => $status->sales_lead_management_id,
                    ]);
                    throw new \Exception("SalesLeadManagement record not found for ID: {$status->sales_lead_management_id}");
                }
            }
    
            // Assign default visited_by and created_by fields if not set
            if (!$status->visited_by && Auth::check()) {
                $status->visited_by = Auth::id();
                Log::info('Visited_by field updated:', ['visited_by' => $status->visited_by]);
            }
    
            if (!$status->created_by && Auth::check()) {
                $status->created_by = Auth::id();
                Log::info('Created_by field updated:', ['created_by' => $status->created_by]);
            }
    
            // Ensure the status is set to a default value if still empty
            if (!$status->status) {
                $status->status = 'School Nurturing';
                Log::info('Default status applied:', ['status' => $status->status]);
            }
    
            // Final log before saving
            Log::info('Final status before save:', ['status' => $status->toArray()]);
        });
    }
    
}
