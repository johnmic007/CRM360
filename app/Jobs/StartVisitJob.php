<?php

namespace App\Jobs;

use App\Models\VisitEntry;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StartVisitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $visitEntry;
    public $data;

    public function __construct(VisitEntry $visitEntry, array $data)
    {
        $this->visitEntry = $visitEntry;
        $this->data = $data;
    }

    public function handle()
    {
        $this->visitEntry->update([
            'travel_type' => $this->data['travel_type'] ?? null,
            'travel_expense' => $this->data['travel_expense'] ?? null,
            'starting_km' => $this->data['starting_km'] ?? null,
            'head_id' => $this->data['head_id'] ?? null,
            'belong_school' => $this->data['belong_school'] ?? null,
            'starting_meter_photo' => $this->data['starting_meter_photo'] ?? null,
            'travel_mode' => $this->data['travel_mode'] ?? null,
            'start_time' => now(),
            'end_time' => null,
        ]);

        Notification::make()
            ->title('Visit Started')
            ->success()
            ->body('The visit has been started successfully.')
            ->send();
    }
}
