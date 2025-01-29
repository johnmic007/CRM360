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
use Illuminate\Support\Carbon;

class StopVisitJob implements ShouldQueue
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
        $startingKm = $this->visitEntry->starting_km;

        if (isset($this->data['ending_km']) && $this->data['ending_km'] <= $startingKm) {
            Notification::make()
                ->title('Validation Error')
                ->danger()
                ->body('The ending kilometers must be greater than the starting kilometers.')
                ->send();
            return;
        }

        $this->visitEntry->update([
            'travel_type' => $this->data['travel_type'] ?? $this->visitEntry->travel_type,
            'travel_expense' => $this->data['travel_expense'] ?? null,
            'travel_bill' => $this->data['travel_bill'] ?? null,
            'starting_km' => $this->data['starting_km'] ?? $this->visitEntry->starting_km,
            'starting_meter_photo' => $this->data['starting_meter_photo'] ?? $this->visitEntry->starting_meter_photo,
            'travel_mode' => $this->data['travel_mode'] ?? $this->visitEntry->travel_mode,
            'ending_km' => $this->data['ending_km'] ?? null,
            'ending_meter_photo' => $this->data['ending_meter_photo'] ?? null,
            'end_time' => now(),
        ]);

        Notification::make()
            ->title('Visit Stopped')
            ->success()
            ->body('The visit has been stopped successfully with the provided ending details.')
            ->send();
    }
}
