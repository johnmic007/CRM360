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
use DateTime;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
        return auth()->user()->hasRole(['admin', 'sales_head', 'head', 'sales_operation', 'sales_operation_head', 'zonal_manager', 'regional _manager', 'head' , 'bdm' , 'bda']);
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole(['admin']);
    }


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                DateTimePicker::make('start_time')
                    ->required()
                    ->seconds(false)
                    ->label('Start Time'),
                    DateTimePicker::make('end_time')
                    // ->required()
                     ->seconds(false)
                    ->label('End Time'),
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->disabled()
                    ->relationship('user', 'name') // Specify the relationship and the display column (e.g., `name`)
                    ->required(),

                Forms\Components\FileUpload::make('starting_meter_photo')
                    ->label('Starting Meter Photo')
                ->disk('s3')
                    ->directory('CRM')
                    ->visible(fn (callable $get) => $get('travel_type') === 'own_vehicle'), // Visible only for 'own_vehicle'

                Forms\Components\FileUpload::make('ending_meter_photo')
                    ->label('Ending Meter Photo')
                ->disk('s3')
                    ->directory('CRM')
                    ->visible(fn (callable $get) => $get('travel_type') === 'own_vehicle'), // Visible only for 'own_vehicle'

                    Forms\Components\TextInput::make('starting_km')
                    ->label('Starting KM')
                    ->visible(fn (callable $get) => $get('travel_type') === 'own_vehicle'), // Visible only for 'own_vehicle'

                Forms\Components\TextInput::make('ending_km')
                    ->label('Ending KM')
                    ->visible(fn (callable $get) => $get('travel_type') === 'own_vehicle'), // Visible only for 'own_vehicle'


                Forms\Components\Select::make('travel_type')
                    ->options([
                        'own_vehicle' => 'Travel by Own Vehicle',
                        'with_colleague' => 'Travel with Colleague',
                    ])
                    ->required()
                    ->label('Travel Type'),
                FileUpload::make('travel_bill')
            ->disk('s3')
                ->directory('CRM')
                    ->label('Travel Bill'),
                Forms\Components\TextInput::make('travel_expense')
                    ->label('Travel Expense'),

                Forms\Components\Select::make('travel_mode')
                    ->options([
                        'car' => 'Car',
                        'bike' => 'Bike',
                    ])
                    ->label('Travel Mode')
                    ->visible(fn (callable $get) => $get('travel_type') === 'own_vehicle'), // Visible only for 'own_vehicle'

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
                    ->label('User') // Updated label
                    ->options(function () {
                        // Fetch all users except those with the 'admin' role
                        return User::whereDoesntHave('roles', function ($query) {
                                $query->where('name', 'admin');
                            })
                            ->pluck('name', 'id') // Fetch users' names and IDs
                            ->all();
                    })
                    ->searchable()
                    ->query(function (Builder $query, $data) {
                        if (!empty($data['value'])) {
                            // Filter by the selected user's ID
                            $query->where('user_id', $data['value']);
                        }
                    }),
                

                SelectFilter::make('travel_type')
                    ->label('Travel Type')
                    ->options([
                        'own_vehicle' => 'Travel by Own Vehicle',
                        'with_colleague' => 'Travel with Colleague',
                    ])

            ])

            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),


            ])
            ->defaultSort('start_time', 'desc')
            ->paginated([10, 25,]);

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
