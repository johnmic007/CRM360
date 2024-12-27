<?php

namespace App\Filament\Resources\VisitEntryResource\Pages;

use App\Filament\Resources\VisitEntryResource;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;

class EditVisitEntry extends EditRecord
{
    protected static string $resource = VisitEntryResource::class;

    protected function getFormSchema(): array
    {
        return [
            // Show running time if the visit has started but not ended
            Forms\Components\Card::make([
                Forms\Components\Placeholder::make('running_time')
                    ->label('Time Running')
                    ->content(function () {
                        if ($this->record->start_time && !$this->record->end_time) {
                            $startTime = Carbon::parse($this->record->start_time);
                            $elapsed = $startTime->diffForHumans(null, true); // Elapsed time in a human-readable format
                            return "Visit started $elapsed ago";
                        }
                        return null;
                    })
                    ->visible(fn () => $this->record->start_time && !$this->record->end_time), // Show only if the visit is ongoing
            ]),

            Forms\Components\Card::make([
                Forms\Components\DateTimePicker::make('start_time')
                    ->label('Start Time')
                    ->disabled(),

                Forms\Components\DateTimePicker::make('end_time')
                    ->label('End Time')
                    ->disabled(),
            ]),
        ];
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('start')
            ->label(function () {
                if ($this->record->start_time && !$this->record->end_time) {
                    // Calculate elapsed time
                    $startTime = \Illuminate\Support\Carbon::parse($this->record->start_time);
                    $now = now();
                    $elapsed = $startTime->diff($now);

                    // Format as HH:MM:SS
                    return sprintf('Running: %02d:%02d:%02d', $elapsed->h, $elapsed->i, $elapsed->s);
                }
                return 'Start';
            })
            ->action('startVisit')
            ->visible(fn () => !$this->record->end_time) // Show while the visit can be started or is running
            ->color('success'),
    
                Actions\Action::make('stop')
                ->label('Stop')
                ->action('stopVisit')
                ->visible(fn () => $this->record->start_time && !$this->record->end_time)
                ->color('danger'),



                Actions\Action::make('calculateWorkingHours')
                ->label(function () {
                    if ($this->record->start_time && $this->record->end_time) {
                        $startTime = \Illuminate\Support\Carbon::parse($this->record->start_time);
                        $endTime = \Illuminate\Support\Carbon::parse($this->record->end_time);
            
                        // Calculate the duration
                        $duration = $startTime->diff($endTime);
                        $formattedDuration = sprintf('%02d:%02d:%02d', $duration->h, $duration->i, $duration->s);
            
                        return "Working Hours: $formattedDuration";
                    }
                    return 'Show Working Hours';
                })
                ->visible(fn () => $this->record->start_time && $this->record->end_time)
                ->action('showWorkingHours')
                ->color('primary'),
            
        ];
    }



    protected function getElapsedTime(): string
{
    if ($this->record->start_time && !$this->record->end_time) {
        $startTime = \Illuminate\Support\Carbon::parse($this->record->start_time);
        $now = now();
        $elapsed = $startTime->diff($now);

        return sprintf('%02d:%02d:%02d', $elapsed->h, $elapsed->i, $elapsed->s);
    }

    return '';
}

    

    public function startVisit()
    {
        $this->save();

       
        
    
        $this->record->update([
            'start_time' => now(),
            'end_time' => null,
        ]);
    
        Notification::make()
            ->title('Visit Started')
            ->success()
            ->body('The visit has started successfully.')
            ->send();
    }

    public function stopVisit()
    {
        $this->record->update([
            'end_time' => now(),
        ]);

        Notification::make()
            ->title('Visit Stopped')
            ->success()
            ->body('The visit has been stopped successfully.')
            ->send();
    }


    public function showWorkingHours()
{
    if ($this->record->start_time && $this->record->end_time) {
        $startTime = \Illuminate\Support\Carbon::parse($this->record->start_time);
        $endTime = \Illuminate\Support\Carbon::parse($this->record->end_time);

        // Calculate the duration
        $duration = $startTime->diff($endTime);
        $formattedDuration = sprintf('%02d:%02d:%02d', $duration->h, $duration->i, $duration->s);

        Notification::make()
            ->title('Working Hours')
            ->success()
            ->body("Total working time: $formattedDuration.")
            ->send();
    } else {
        Notification::make()
            ->title('Error')
            ->danger()
            ->body('Start time or end time is missing.')
            ->send();
    }
}

}
