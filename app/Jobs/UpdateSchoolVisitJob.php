<?php

namespace App\Jobs;

use App\Models\SchoolVisit;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateSchoolVisitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $visit;
    public $data;

    public function __construct(SchoolVisit $visit, array $data)
    {
        $this->visit = $visit;
        $this->data = $data;
    }

    public function handle()
    {
        $this->visit->update([
            'status' => $this->data['status'],
            'remarks' => $this->data['remarks'],
            'follow_up_date' => $this->data['follow_up_date'],
            'reschedule_date' => $this->data['reschedule_date'] ?? null,
            'potential_meet' => $this->data['potential_meet'],
            'is_book_issued' => $this->data['is_book_issued'],
        ]);

        Notification::make()
            ->title('School Visit Updated')
            ->success()
            ->body('The school visit has been successfully updated.')
            ->send();
    }
}
