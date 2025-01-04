<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitEntryResource\RelationManagers\SchoolVisitRelationManager;
use App\Filament\Resources\VisitReportResource\Pages;
use App\Filament\Resources\VisitReportResource\RelationManagers;
use App\Models\VisitEntry;
use App\Models\VisitReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use App\Models\User;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VisitReportResource extends Resource
{
    protected static ?string $model = VisitEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Visit Report';

    protected static ?string $navigationGroup = 'Reports';


    protected static ?string $pluralLabel = 'Visit Report';

  
    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'sales_operation' , 'sales_operation_head' , ]);
    }



    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('start_time')
                    ->required()
                    ->label('Start Time'),
                Forms\Components\TextInput::make('end_time')
                    ->required()
                    ->label('End Time'),
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->disabled()
                    ->relationship('user', 'name') // Specify the relationship and the display column (e.g., `name`)
                    ->required(),

                Forms\Components\FileUpload::make('starting_meter_photo')
                    ->label('Starting Meter Photo'),
                Forms\Components\FileUpload::make('ending_meter_photo')
                    ->label('Ending Meter Photo'),
                Forms\Components\Select::make('travel_type')
                    ->options([
                        'own_vehicle' => 'Travel by Own Vehicle',
                        'with_colleague' => 'Travel with Colleague',
                    ])
                    ->required()
                    ->label('Travel Type'),
                Forms\Components\TextInput::make('travel_bill')
                    ->label('Travel Bill'),
                Forms\Components\TextInput::make('travel_expense')
                    ->label('Travel Expense'),
                Forms\Components\TextInput::make('starting_km')
                    ->label('Starting KM'),
                Forms\Components\TextInput::make('ending_km')
                    ->label('Ending KM'),
                Forms\Components\Select::make('travel_mode')
                    ->options([
                        'car' => 'Car',
                        'bike' => 'Bike',
                    ])
                    ->label('Travel Mode'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start Time')
                    ->dateTime(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('End Time')
                    ->dateTime(),

                Tables\Columns\TextColumn::make('working_hours')
                    ->label('Working Hours')
                    ->getStateUsing(function ($record) {
                        if ($record->start_time && $record->end_time) {
                            $start = Carbon::parse($record->start_time);
                            $end = Carbon::parse($record->end_time);
                            $duration = $start->diff($end);

                            return sprintf('%02d:%02d:%02d', $duration->h, $duration->i, $duration->s);
                        }
                        return 'N/A';
                    }),

                Tables\Columns\BooleanColumn::make('completed')
                    ->label('Visit Completed')
                    ->getStateUsing(fn($record) => !is_null($record->end_time)),

                Tables\Columns\TextColumn::make('lead_count')
                    ->label('Lead Count')
                    ->getStateUsing(fn($record) => $record->leadStatuses()->count()),

                Tables\Columns\TextColumn::make('potential_meet_count')
                    ->label('Potential Meets')
                    ->getStateUsing(fn($record) => $record->leadStatuses()->sum('potential_meet')),
            ])
            ->filters([
             
                Tables\Filters\Filter::make('completed')
                ->label('Completed Visits')
                ->query(fn(Builder $query) => $query->whereNotNull('end_time')),

            Tables\Filters\Filter::make('ongoing')
                ->label('Ongoing Visits')
                ->query(fn(Builder $query) => $query->whereNull('end_time')),

                Tables\Filters\Filter::make('working_hours')
                    ->label('Working Hours')
                    ->form([
                        Forms\Components\TextInput::make('min_hours')
                            ->label('Minimum Hours')
                            ->numeric(),
                        Forms\Components\TextInput::make('max_hours')
                            ->label('Maximum Hours')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['min_hours'])) {
                            $query->whereRaw('TIMESTAMPDIFF(HOUR, start_time, end_time) >= ?', [$data['min_hours']]);
                        }
                        if (!empty($data['max_hours'])) {
                            $query->whereRaw('TIMESTAMPDIFF(HOUR, start_time, end_time) <= ?', [$data['max_hours']]);
                        }
                    })
                    ->indicateUsing(function (array $data) {
                        $indicators = [];

                        if (!empty($data['min_hours'])) {
                            $indicators[] = 'Min Hours: ' . $data['min_hours'];
                        }

                        if (!empty($data['max_hours'])) {
                            $indicators[] = 'Max Hours: ' . $data['max_hours'];
                        }

                        return implode(', ', $indicators);
                    }),




                // ...


              




                Tables\Filters\Filter::make('date_range')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            // Use today's date at the start of the day
                            ->default(Carbon::today()->startOfDay()),

                        DatePicker::make('end_date')
                            ->label('End Date')
                            // Use tomorrow's date at the end of the day
                            ->default(Carbon::tomorrow()->endOfDay()),
                    ])
                    ->label('Date Range')
                    ->query(function (Builder $query, $data) {
                        if (!empty($data['start_date']) && !empty($data['end_date'])) {
                            $query->whereBetween('start_time', [$data['start_date'], $data['end_date']]);
                        }
                    })
                    ->indicateUsing(function ($data) {
                        if (!empty($data['start_date']) && !empty($data['end_date'])) {
                            return 'Date: ' . Carbon::parse($data['start_date'])->format('d-m-Y')
                                . ' to '
                                . Carbon::parse($data['end_date'])->format('d-m-Y');
                        }

                        return null;
                    }),


                    SelectFilter::make('selected_user')
                    ->label('User Team Visits') // Shortened label
                    ->options(function () {
                        // Fetch users with specific roles (e.g., 'BDA' and 'BDM')
                        return User::role(['zonal_manager', 'bdm', 'regional_manager']) // Use the `role` method from Spatie's package
                            ->pluck('name', 'id') // Fetch users' names and IDs
                            ->all();
                    })
                    ->searchable()
                    ->query(function (Builder $query, $data) {
                        // Check if the 'value' key exists in the data and retrieve its value
                        if (empty($data['value'])) {
                            // If no value is provided, skip the filter logic
                            return;
                        }

                        $selectedUserId = $data['value']; // Extract the selected user ID

                        // Fetch the selected user
                        $selectedUser = User::find($selectedUserId);

                        if ($selectedUser) {
                            // Fetch subordinate IDs
                            try {
                                $subordinateIds = $selectedUser->getAllSubordinateIds();
                                $subordinateIds[] = $selectedUser->id; // Include the selected user's ID

                                $query->whereIn('user_id', $subordinateIds); // Apply filter
                            } catch (\Exception $e) {
                                // Log or handle any errors
                                logger()->error('Error fetching subordinate IDs:', ['message' => $e->getMessage()]);
                            }
                        } else {
                            // Log when the selected user cannot be found
                            logger()->warning('User not found for selected ID:', ['user_id' => $selectedUserId]);
                        }
                    }),
                  
            ])

            ->actions([
                Tables\Actions\ViewAction::make(),

            ])
            ->defaultSort('start_time', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            SchoolVisitRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitReports::route('/'),
            'create' => Pages\CreateVisitReport::route('/create'),
            'edit' => Pages\EditVisitReport::route('/{record}/edit'),
            'view' => Pages\ViewVisitReportResource::route('/{record}'),

        ];
    }
}
