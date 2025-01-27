<?php

namespace App\Filament\Resources\VisitEntryResource\Pages;

use App\Filament\Resources\VisitEntryResource;
use App\Filament\Resources\VisitEntryResource\RelationManagers\SchoolVisitRelationManager;
use App\Models\TrainerVisit;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Actions;
use Filament\Forms\Components\Checkbox;
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
        if (
            $this->record && $this->record->start_time &&
            ($this->record->travel_type === 'own_vehicle' || $this->record->belong_school)
        ) {
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
                            'with_head' => 'Travel with Head',

                        ])
                        ->reactive()
                        ->required(),




                    Forms\Components\TextInput::make('starting_km')
                        ->label('Starting KM')
                        ->numeric()
                        ->helperText('Enter the starting kilometers.')
                        ->required()
                        ->hidden(fn($get) => $get('travel_type') !== 'own_vehicle'),


                    Checkbox::make('belong_school')
                        ->label('Is this school belongs to you ')
                        ->hidden(fn($get) => $get('travel_type') !== 'with_head'),


                    Select::make('head_id')
                        ->label('Select Head you travel with')
                        ->options(function () {
                            return \App\Models\User::role(['zonal_manager', 'regional_manager', 'sales_operation_head'])
                                ->get()
                                ->mapWithKeys(function ($user) {
                                    return [$user->id => "{$user->name} ({$user->email})"];
                                });
                        })
                        ->searchable()
                        ->hidden(fn($get) => $get('travel_type') !== 'with_head')
                        ->helperText('Select a user with the role Zonal Manager, Senior Manager, or Sales Head.'),


                    Select::make('travel_mode')
                        ->label('Travel Mode')
                        ->options([
                            'car' => 'Car',
                            'bike' => 'Bike',
                        ])
                        ->required()
                        ->hidden(fn($get) => $get('travel_type') !== 'own_vehicle'),

                    Forms\Components\FileUpload::make('starting_meter_photo')
                        ->disk('s3')
                        ->directory('CRM')
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
                        ->visible(fn() => $this->record->travel_type === 'own_vehicle')
                        ->columnSpan('full'), // Make the input span the full width of the form
                    Forms\Components\FileUpload::make('ending_meter_photo')
                        ->disk('s3')
                        ->directory('CRM')
                        ->label('Ending Meter Photo')
                        ->required()

                        ->helperText('Upload a photo of the ending meter.')
                        ->visible(fn() => $this->record->travel_type === 'own_vehicle')
                        ->columnSpan('full'), // Make the input span the full width of the form


                    Forms\Components\TextInput::make('travel_expense')
                        ->label('Travel Expense')
                        ->numeric()
                        ->helperText('Provide the travel expense incurred.')
                        ->visible(fn() => $this->record->travel_type === 'with_colleague'), // Only show if travel type is 'with_colleague'

                    Forms\Components\FileUpload::make('travel_bill')
                        ->label('Travel Bill (Bus/Train)')
                        ->visible(fn() => $this->record->travel_type === 'with_colleague'),
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
            'starting_km' => $data['starting_km'] ?? null,
            'head_id' => $data['head_id'] ?? null,
            'belong_school' => $data['belong_school'] ?? null,
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

        $startingKm = $this->record->starting_km;

        // Validate that ending_km is greater than starting_km
        if (isset($data['ending_km']) && $data['ending_km'] <= $startingKm) {
            Notification::make()
                ->title('Validation Error')
                ->danger()
                ->body('The ending kilometers must be greater than the starting kilometers.')
                ->send();

            return; // Stop execution if validation fails
        }

        // Combine start and stop data and save everything together
        $this->record->update([
            // Data collected during 'start'
            'travel_type' => $data['travel_type'] ?? $this->record->travel_type,
            'travel_expense' => $data['travel_expense'] ?? null,
            'travel_bill' => $data['travel_bill'] ?? null, // Assuming the first uploaded file
            'starting_km' => $data['starting_km'] ?? $this->record->starting_km,
            'starting_meter_photo' => $data['starting_meter_photo'] ?? $this->record->starting_meter_photo,
            'travel_mode' => $data['travel_mode'] ?? $this->record->travel_mode,

            // Data collected during 'stop'
            'ending_km' => $data['ending_km'] ?? null,
            'ending_meter_photo' => $data['ending_meter_photo'] ?? null,
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
