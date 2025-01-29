<?php

namespace App\Jobs;

use App\Models\SchoolVisit;
use App\Models\SalesLeadManagement;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StoreSchoolVisitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        $visit = SchoolVisit::create([
            'state_id' => $this->data['state_id'],
            'district_id' => $this->data['district_id'],
            'block_id' => $this->data['block_id'],
            'school_id' => $this->data['school_id'],
            'status' => $this->data['status'],
            'remarks' => $this->data['remarks'],
            'contacted_person' => $this->data['contacted_person'],
            'contacted_person_designation' => $this->data['contacted_person_designation'],
            'contact_number' => $this->data['contact_number'],
            'visited_date' => $this->data['visited_date'],
            'follow_up_date' => $this->data['follow_up_date'],
            'reschedule_date' => $this->data['reschedule_date'] ?? null,
            'potential_meet' => $this->data['potential_meet'],
            'is_book_issued' => $this->data['is_book_issued'],
        ]);

        // If it's a new lead, create SalesLeadManagement entry
        if (!$visit->sales_lead_management_id) {
            $salesLead = SalesLeadManagement::create([
                'school_id' => $this->data['school_id'],
                'state_id' => $this->data['state_id'],
                'district_id' => $this->data['district_id'],
                'block_id' => $this->data['block_id'],
                'status' => $this->data['status'],
                'allocated_to' => auth()->id(),
            ]);

            $visit->update(['sales_lead_management_id' => $salesLead->id]);
        }

        Notification::make()
            ->title('School Visit Logged')
            ->success()
            ->body('The school visit has been successfully recorded.')
            ->send();
    }
}
