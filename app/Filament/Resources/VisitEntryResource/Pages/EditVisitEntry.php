<?php

namespace App\Filament\Resources\VisitEntryResource\Pages;

use App\Filament\Resources\VisitEntryResource;
use App\Filament\Resources\VisitEntryResource\RelationManagers\SchoolVisitRelationManager;
use App\Models\TrainerVisit;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Carbon;

class EditVisitEntry extends EditRecord
{
    protected static string $resource = VisitEntryResource::class;

    protected function getFormActions(): array
    {
        return []; // Remove all default form actions
    }

    public function getRelationManagers(): array
    {
        // Only load relation managers if `start_time` is set
        if ($this->record && $this->record->start_time) {
            return [
                SchoolVisitRelationManager::class,
                // Add other relation managers here if needed
            ];
        }

        // Otherwise, return an empty array
        return [];
    }

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
                    ->visible(fn() => $this->record->start_time && !$this->record->end_time), // Show only if the visit is ongoing
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
                ->label('Start')
                ->modalHeading('Start Visit')
                ->modalSubheading('Provide the starting details of the visit.')
                ->form([
                    Select::make('travel_type')
                        ->label('Travel Type')
                        ->options([
                            'own_vehicle' => 'Travel by Own Vehicle',
                            'with_colleague' => 'Travel with Colleague',
                        ])
                        ->reactive()
                        ->required(),

                    Forms\Components\FileUpload::make('travel_bill')
                        ->label('Upload Travel Bill (Bus/Train)')
                        ->hidden(fn($get) => $get('travel_type') !== 'with_colleague'),

                    Forms\Components\TextInput::make('travel_expense')
                        ->label('Travel Expense')
                        ->numeric()
                        ->hidden(fn($get) => $get('travel_type') !== 'with_colleague'),

                    Forms\Components\TextInput::make('starting_km')
                        ->label('Starting KM')
                        ->numeric()
                        ->helperText('Enter the starting kilometers.')
                        ->required()
                        ->hidden(fn($get) => $get('travel_type') !== 'own_vehicle'),

                    Select::make('travel_mode')
                        ->label('Travel Mode')
                        ->options([
                            'car' => 'Car',
                            'bike' => 'Bike',
                        ])
                        ->required()
                        ->hidden(fn($get) => $get('travel_type') !== 'own_vehicle'),

                    Forms\Components\FileUpload::make('starting_meter_photo')
                        ->label('Starting Meter Photo')
                        ->required()
                        ->helperText('Upload a photo of the starting meter.')
                        ->hidden(fn($get) => $get('travel_type') !== 'own_vehicle'),
                ])
                ->action(fn(array $data) => $this->submitStartVisit($data)) // Ensure data is passed to the method
                ->visible(fn() => !$this->record->start_time) // Only visible if start_time is null
                ->color('success'),

                Actions\Action::make('stop')
                ->label('End the Visit')
                ->modalHeading('Provide Ending Details')
                ->modalSubheading('Please provide the ending details before stopping the visit.')
                ->icon('heroicon-o-stop') // Add an appropriate stop icon
                ->color('danger') // Use a danger color for emphasis
                ->size('lg') // Make the button larger
                ->form([
                    Forms\Components\TextInput::make('ending_km')
                        ->label('Ending KM')
                        ->numeric()
                        ->required()
                        ->helperText('Enter the ending kilometers.')
                        ->columnSpan('full'), // Make the input span the full width of the form
                    Forms\Components\FileUpload::make('ending_meter_photo')
                        ->label('Ending Meter Photo')
                        ->required()
                        ->helperText('Upload a photo of the ending meter.')
                        ->columnSpan('full'), // Make the input span the full width of the form
                ])
                ->action(fn(array $data) => $this->submitStopVisit($data))
                ->visible(fn() => $this->record->start_time && !$this->record->end_time)
                ->color('danger'),

            Actions\Action::make('calculateWorkingHours')
                ->label(fn() => $this->getWorkingHoursLabel()) // Use a dedicated method to calculate and return the label
                ->visible(fn() => $this->record->start_time && $this->record->end_time)
                ->color('primary'),

        ];
    }

    public function submitStartVisit(array $data = [])
    {
        // Update the VisitEntry record with the provided data
        $this->record->update([
            'travel_type' => $data['travel_type'] ?? null,
            'travel_expense' => $data['travel_expense'] ?? null,
            'travel_bill' => $data['travel_bill'][0] ?? null, // Assuming the first uploaded file
            'starting_km' => $data['starting_km'] ?? null,

                'starting_meter_photo' => $data['starting_meter_photo'] ?? null, // Save raw array

            'travel_mode' => $data['travel_mode'] ?? null,
            'start_time' => now(), // Set the start time
            'end_time' => null, // Clear the end time
        ]);

        Notification::make()
            ->title('Visit Started')
            ->success()
            ->body('The visit has been started successfully.')
            ->send();
    }

    public function submitStopVisit(array $data)
    {
        // Save the provided data directly into the current VisitEntry record
        $this->record->update([
            'ending_km' => $data['ending_km'] ?? null,
            'ending_meter_photo' => $data['ending_meter_photo'] ?? null, // Save raw array

            'end_time' => now(), // Set the end time
        ]);

        Notification::make()
            ->title('Visit Stopped')
            ->success()
            ->body('The visit has been stopped successfully with the provided ending details.')
            ->send();
    }


    protected function getWorkingHoursLabel(): string
    {
        if ($this->record->start_time && $this->record->end_time) {
            $startTime = Carbon::parse($this->record->start_time);
            $endTime = Carbon::parse($this->record->end_time);

            // Calculate the duration
            $duration = $startTime->diff($endTime);
            $formattedDuration = sprintf('%02d:%02d:%02d', $duration->h, $duration->i, $duration->s);

            return "Working Hours: $formattedDuration";
        }

        return 'Calculate Working Hours';
    }
}
