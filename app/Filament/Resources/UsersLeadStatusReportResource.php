<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\UsersLeadStatusReportResource\Pages\ListUsersLeadStatusReports;

class UsersLeadStatusReportResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Users Lead Status Report';

    protected static ?string $pluralLabel = 'Users Lead Status Report';

    protected static ?string $navigationGroup = 'Reports';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'head', 'sales_operation', 'sales_operation_head', 'zonal_manager', 'regional _manager', 'head' , 'bdm' , 'bda']);
    }


    public static function table(Table $table): Table
    {
        return $table
            // 1) Define the columns we want to display
            ->columns([
                TextColumn::make('name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),

                // Example: Count of all SalesLeadStatus (visited) in the date range
                TextColumn::make('total_visits_in_range')
                    ->label('Total Visits')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $startDate = data_get($filters['date_range'], 'start_date');
                        $endDate   = data_get($filters['date_range'], 'end_date');

                        // Query the user's lead statuses
                        $query = $record->salesLeadStatuses();

                        if ($startDate) {
                            $query->whereDate('visited_date', '>=', $startDate);
                        }
                        if ($endDate) {
                            $query->whereDate('visited_date', '<=', $endDate);
                        }

                        return $query->count();
                    }),

                // Example: Potential meets count
                TextColumn::make('potential_meet_in_range')
                    ->label('Potential Meets')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $startDate = data_get($filters['date_range'], 'start_date');
                        $endDate   = data_get($filters['date_range'], 'end_date');

                        $query = $record->salesLeadStatuses()->where('potential_meet', true);

                        if ($startDate) {
                            $query->whereDate('visited_date', '>=', $startDate);
                        }
                        if ($endDate) {
                            $query->whereDate('visited_date', '<=', $endDate);
                        }

                        return $query->count();
                    }),

                // Example: Count of "closed" leads in the date range
                TextColumn::make('School Nurturing')
                    ->label('School Nurturing')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $startDate = data_get($filters['date_range'], 'start_date');
                        $endDate   = data_get($filters['date_range'], 'end_date');

                        $query = $record->salesLeadStatuses()->where('status', 'School Nurturing');

                        if ($startDate) {
                            $query->whereDate('visited_date', '>=', $startDate);
                        }
                        if ($endDate) {
                            $query->whereDate('visited_date', '<=', $endDate);
                        }

                        return $query->count();
                    }),


                    TextColumn::make('Demo Completed')
                    ->label('Demo Completed')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $startDate = data_get($filters['date_range'], 'start_date');
                        $endDate   = data_get($filters['date_range'], 'end_date');

                        $query = $record->salesLeadStatuses()->where('status', 'Demo Completed');

                        if ($startDate) {
                            $query->whereDate('visited_date', '>=', $startDate);
                        }
                        if ($endDate) {
                            $query->whereDate('visited_date', '<=', $endDate);
                        }

                        return $query->count();
                    }),

                    TextColumn::make('Demo Schedule')
                    ->label('Demo Schedule')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $startDate = data_get($filters['date_range'], 'start_date');
                        $endDate   = data_get($filters['date_range'], 'end_date');

                        $query = $record->salesLeadStatuses()->where('status', 'Demo reschedule');

                        if ($startDate) {
                            $query->whereDate('visited_date', '>=', $startDate);
                        }
                        if ($endDate) {
                            $query->whereDate('visited_date', '<=', $endDate);
                        }

                        return $query->count();
                    }),

                    TextColumn::make('School Nurturing')
                    ->label('School Nurturing')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $startDate = data_get($filters['date_range'], 'start_date');
                        $endDate   = data_get($filters['date_range'], 'end_date');

                        $query = $record->salesLeadStatuses()->where('status', 'School Nurturing');

                        if ($startDate) {
                            $query->whereDate('visited_date', '>=', $startDate);
                        }
                        if ($endDate) {
                            $query->whereDate('visited_date', '<=', $endDate);
                        }

                        return $query->count();
                    }),
                // Example: Count of "deal_won" leads in the date range

                TextColumn::make('deal_lost_in_range')
                    ->label('Deals lost')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $startDate = data_get($filters['date_range'], 'start_date');
                        $endDate   = data_get($filters['date_range'], 'end_date');

                        $query = $record->salesLeadStatuses()->where('status', 'deal_lost');

                        if ($startDate) {
                            $query->whereDate('visited_date', '>=', $startDate);
                        }
                        if ($endDate) {
                            $query->whereDate('visited_date', '<=', $endDate);
                        }

                        return $query->count();
                    }),


                TextColumn::make('deal_won_in_range')
                    ->label('Deals Won')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $startDate = data_get($filters['date_range'], 'start_date');
                        $endDate   = data_get($filters['date_range'], 'end_date');

                        $query = $record->salesLeadStatuses()->where('status', 'deal_won');

                        if ($startDate) {
                            $query->whereDate('visited_date', '>=', $startDate);
                        }
                        if ($endDate) {
                            $query->whereDate('visited_date', '<=', $endDate);
                        }

                        return $query->count();
                    }),
            ])

            // 2) Define our filters (similar to date_range in your Performance Report)
            ->filters([
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->closeOnDateSelection(false),

                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->closeOnDateSelection(false),
                    ])
                    ->indicateUsing(function ($data) {
                        $start = data_get($data, 'start_date') 
                            ? Carbon::parse($data['start_date'])->format('d M Y') 
                            : '...';
                        $end   = data_get($data, 'end_date') 
                            ? Carbon::parse($data['end_date'])->format('d M Y') 
                            : '...';

                        return ($data['start_date'] ?? null) || ($data['end_date'] ?? null)
                            ? "From {$start} to {$end}"
                            : null;
                    }),
                ]);

            // 3) Choose pagination or other table configurations
            // ->paginate(10);
    }

    /**
     * Define relations if needed
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Define pages
     */
    public static function getPages(): array
    {
        return [
            'index' => ListUsersLeadStatusReports::route('/'),
        ];
    }
}
