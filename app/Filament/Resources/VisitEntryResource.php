<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitEntryResource\Pages;
use App\Models\Block;
use App\Models\District;
use App\Models\SalesLeadManagement;
use App\Models\VisitEntry;
use App\Models\TrainerVisit;
use App\Models\SalesLeadStatus;
use App\Models\School;
use App\Models\State;
use Illuminate\Support\Facades\DB;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Hidden;

class VisitEntryResource extends Resource
{
    protected static ?string $model = VisitEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';




    public static function getActions(): array
    {
        return [
            // Define the "Start" action here
            Action::make('start')
                ->label('Start')
                ->color('success')

        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Section for Starting Details



            Hidden::make('user_id')
            ->default(fn () => auth()->id()),


            Select::make('travel_type')
    ->label('Travel Type')
    ->options([
        'own_vehicle' => 'Travel by Own Vehicle',
        'with_colleague' => 'Travel with Colleague',
    ])
    ->reactive()
    ->required()
    ->disabled(fn (callable $get) => $get('start_time') !== null) // Disable if start_time is not null
    ->dehydrated(true), // Ensure the value is included in the database save






            Forms\Components\Section::make('Travel Bill and Expense')
                ->description('Provide details for travel expenses.')
                ->schema([
                    Forms\Components\FileUpload::make('travel_bill')
                        ->label('Upload Travel Bill (Bus/Train)')
                        ->directory('travel-bills'),

                    Forms\Components\TextInput::make('travel_expense')
                        ->label('Travel Expense')
                        ->numeric(),
                ])
                ->hidden(fn($get) => $get('travel_type') !== 'with_colleague'),

            Forms\Components\Section::make('Starting Details')
                ->description('Provide the starting details of the visit.')
                ->schema([
                    Forms\Components\TextInput::make('starting_km')
                        ->label('Starting KM')
                        ->numeric()
                        ->helperText('Enter the starting kilometers.')
                        ->reactive()
                        ->required()
                        ->afterStateHydrated(function ($state, $set, $get) {
                            $trainerVisit = TrainerVisit::where('visit_entry_id', $get('id'))->first();
                            if ($trainerVisit) {
                                $set('starting_km', $trainerVisit->starting_km);
                            }
                        })
                        
                        ->afterStateUpdated(function ($state, $get) {
                            $trainerVisit = TrainerVisit::firstOrNew(['visit_entry_id' => $get('id')]);
                            $trainerVisit->starting_km = $state;
                            $trainerVisit->save();
                        }),

                        Select::make('travel_mode')
                            ->label('Travel Mode')
                            ->options([
                                'car' => 'Car',
                                'bike' => 'Bike',
                            ])
                        ->required(),

                    Forms\Components\FileUpload::make('starting_meter_photo')
                        ->label('Starting Meter Photo')
                        ->directory('trainer-visits')
                        ->required()
                        ->helperText('Upload a photo of the starting meter.')
                        ->default(function (callable $get) {
                            // Fetch the trainer visit record
                            $trainerVisit = TrainerVisit::where('visit_entry_id', $get('id'))->first();

                            // Return the photo path if it exists and is a valid string, otherwise return null
                            return $trainerVisit && is_string($trainerVisit->starting_meter_photo)
                                ? $trainerVisit->starting_meter_photo
                                : null;
                        })
                        ->afterStateUpdated(function ($state, $get) {
                            // Update or create a trainer visit record
                            $trainerVisit = TrainerVisit::firstOrNew(['visit_entry_id' => $get('id')]);
                            $trainerVisit->starting_meter_photo = $state ?? null; // Handle null state gracefully
                            $trainerVisit->save();
                        })
                        ->nullable() // Allow null to handle cases where the photo is missing
                        ->reactive(), // Ensure it updates dynamically

                ])
                ->hidden(fn($record, $get) => ($record && $record->end_time !== null) || $get('travel_type') === 'with_colleague'),

            // Section for Ending Details
            Forms\Components\Section::make('Ending Details')
                ->description('Provide the ending details of the visit.')
                ->schema([
                    Forms\Components\TextInput::make('ending_km')
                        ->label('Ending KM')
                        ->numeric()
                        ->required()
                        ->helperText('Enter the ending kilometers.')
                        ->reactive()
                        ->afterStateHydrated(function ($state, $set, $get) {
                            $trainerVisit = TrainerVisit::where('visit_entry_id', $get('id'))->first();
                            if ($trainerVisit) {
                                $set('ending_km', $trainerVisit->ending_km);
                            }
                        })
                        ->afterStateUpdated(function ($state, $get) {
                            $trainerVisit = TrainerVisit::firstOrNew(['visit_entry_id' => $get('id')]);
                            $trainerVisit->ending_km = $state;
                            $trainerVisit->save();
                        }),

                    Forms\Components\FileUpload::make('ending_meter_photo')
                        ->label('Ending Meter Photo')
                        ->required()
                        ->directory('trainer-visits')
                        ->helperText('Upload a photo of the ending meter.'),
                     
                ])
                ->hidden(fn($record, callable $get) => !$record || $record->end_time === null || $get('travel_type') === 'with_colleague'),

            // Section for Follow-Up Entries
            Forms\Components\Repeater::make('leadStatuses')
                ->relationship('leadStatuses')
                ->label('Follow-Ups')
                ->schema([
                    Forms\Components\Select::make('state_id')
                        ->label('State')
                        ->options(function () {
                            $allocatedStates = auth()->user()->allocated_states ?? [];
                            return State::whereIn('id', $allocatedStates)
                                ->pluck('name', 'id');
                        })
                        ->reactive()
                        ->afterStateUpdated(fn(callable $set) => $set('district_id', null)),

                    Forms\Components\Select::make('district_id')
                        ->label('District')
                        ->options(function () {
                            $allocatedDistricts = auth()->user()->allocated_districts ?? [];
                            return District::whereIn('id', $allocatedDistricts)
                                ->pluck('name', 'id');
                        })
                        ->reactive()
                        ->afterStateUpdated(fn(callable $set) => $set('block_id', null)),

                    Forms\Components\Select::make('block_id')
                        ->label('Block')
                        ->options(function (callable $get) {
                            $districtId = $get('district_id');
                            if (!$districtId) {
                                return [];
                            }
                            return Block::where('district_id', $districtId)->pluck('name', 'id')->toArray();
                        })
                        ->reactive()
                        ->afterStateUpdated(fn(callable $set) => $set('school_id', null)),



                    Forms\Components\Select::make('school_id')
                        ->label('School')
                        ->options(function (callable $get) {
                            $blockId = $get('block_id');
                            if (!$blockId) {
                                return [];
                            }
                            return School::where('block_id', $blockId)->pluck('name', 'id');
                        })
                        ->reactive()
                        ->searchable()
                        ->required()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            if (!$state) {
                                return;
                            }

                            $assignedSchools = DB::table('school_user')->where('school_id', $state)->exists();

                            if (!$assignedSchools) {
                                $salesLeadManagement = SalesLeadManagement::firstOrCreate([
                                    'school_id' => $state,
                                    'district_id' => $get('district_id'),
                                    'block_id' => $get('block_id'),
                                    'state_id' => $get('state_id'),
                                    'status' => 'School Nurturing',
                                    'allocated_to' => auth()->id(),
                                    'company_id' => auth()->user()->company_id ?? null,
                                ]);

                                DB::table('school_user')->insert([
                                    'school_id' => $state,
                                    'user_id' => auth()->id(),
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);

                                // Pass the SalesLeadManagement ID to another field
                                $set('sales_lead_management_id', $salesLeadManagement->id);

                                $set('status', 'School Nurturing');
                            } else {
                                $status = SalesLeadManagement::where('school_id', $state)
                                    ->where('allocated_to', auth()->id())
                                    ->value('status');

                                $salesLeadManagementId = SalesLeadManagement::where('school_id', $state)
                                    ->where('allocated_to', auth()->id())
                                    ->value('id');

                                // Pass the existing SalesLeadManagement ID
                                $set('sales_lead_management_id', $salesLeadManagementId);

                                $set('status', $status ?? 'No Status Found');
                            }
                        }),

                    Hidden::make('sales_lead_management_id')
                        ->label('Sales Lead Management ID'),






                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(function (callable $get) {
                            $currentStatus = $get('status');

                            // dd($currentStatus);

                            if ($currentStatus === 'School Nurturing') {
                                return [
                                    'School Nurturing' => 'School Nurturing',
                                    'Demo reschedule' => 'Demo schedule',
                                ];
                            }

                            if ($currentStatus === 'Demo reschedule') {
                                return [
                                    'Demo reschedule' => 'Demo schedule',
                                    'Demo Completed' => 'Demo Completed',
                                ];
                            }

                            if ($currentStatus === 'Demo Completed') {
                                return [
                                    'Demo Completed' => 'Demo Completed',

                                    'deal_won' => 'Deal Won',
                                    'deal_lost' => 'Deal Lost',
                                ];
                            }

                            if ($currentStatus === 'deal_won') {
                                return [

                                    'deal_won' => 'Deal Won',
                                    'support' => 'Support',
                                ];
                            }

                            if ($currentStatus === 'deal_lost') {
                                return [

                                    'deal_lost' => 'Deal Lost',
                                    'support' => 'Support',
                                ];
                            }

                            return [
                                'School Nurturing' => 'School Nurturing',
                                'Demo reschedule' => 'Demo schedule',
                                'deal_won' => 'Deal Won',
                                'deal_lost' => 'Deal Lost',
                            ];
                        })
                        ->reactive()
                        ->helperText('Specify the lead status.')
                        ->afterStateUpdated(function ($state, callable $get) {
                            // Automatically update SalesLeadManagement status
                            $salesLeadManagementId = $get('sales_lead_management_id');
                            if ($salesLeadManagementId) {
                                $salesLeadManagement = SalesLeadManagement::find($salesLeadManagementId);
                                if ($salesLeadManagement) {
                                    $salesLeadManagement->update(['status' => $state]);
                                }
                            }
                        }),

                    Forms\Components\Textarea::make('remarks')
                        ->label('Remarks')
                        ->required()
                        ->visible(fn(callable $get) => in_array($get('status'), ['School Nurturing', 'Demo reschedule'])),

                    Forms\Components\TextInput::make('contacted_person')
                        ->label('Contacted Person')
                        ->required()
                        ->visible(fn(callable $get) => $get('status') === 'School Nurturing'),

                    Forms\Components\TextInput::make('contacted_person_designation')
                        ->label('Contacted Person Designation')
                        ->visible(fn(callable $get) => $get('status') === 'School Nurturing'),

                    Forms\Components\Toggle::make('potential_meet')
                        ->label('Potential Meet')
                        ->visible(fn(callable $get) => in_array($get('status'), ['School Nurturing', 'Demo reschedule'])),

                    Forms\Components\DatePicker::make('visited_date')
                        ->label('Visited Date')
                        ->default(now())
                        ->required()
                        ->visible(fn(callable $get) => in_array($get('status'), ['School Nurturing', 'Demo reschedule'])),

                    Forms\Components\DatePicker::make('follow_up_date')
                        ->label('Follow-Up Date')
                        ->visible(fn(callable $get) => $get('status') === 'School Nurturing'),

                    Forms\Components\DatePicker::make('reschedule_date')
                        ->label('Reschedule Date')
                        ->visible(fn(callable $get) => $get('status') === 'Demo reschedule'),

                    Forms\Components\Radio::make('status')
                        ->label('Deal Status')
                        ->options([
                            'deal_won' => 'Deal Won',
                            'deal_lost' => 'Deal Lost',
                        ])
                        ->required()
                        ->helperText('Select whether the deal was won or lost.')
                        ->visible(fn(callable $get) => $get('status') === 'Demo Completed'),
                ])
                ->createItemButtonLabel('Add Follow-Up')
                ->columns(2)
                ->hidden(fn($record, $get) => ($record && $record->end_time !== null) || $get('travel_type') === 'with_colleague')
                ->columnSpan('full'), // Hide if `end_time` is set

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start Time')
                    ->dateTime(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('End Time')
                    ->dateTime(),

                Tables\Columns\TextColumn::make('trainerVisit.starting_km')
                    ->label('Starting KM'),

                Tables\Columns\TextColumn::make('trainerVisit.ending_km')
                    ->label('Ending KM'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitEntries::route('/'),
            'create' => Pages\CreateVisitEntry::route('/create'),
            'edit' => Pages\EditVisitEntry::route('/{record}/edit'),
        ];
    }
}
