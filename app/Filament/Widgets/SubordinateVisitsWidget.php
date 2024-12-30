<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use App\Models\VisitEntry;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;

class SubordinateVisitsWidget extends BaseWidget
{
    protected static ?string $heading = 'Subordinate Visits Summary';

    protected int | string | array $columnSpan = 'full';


    public static function canView(): bool
    {
        return auth()->user()->hasRole(['admin' , 'head' , 'bdm' , 'zonal_manager' , 'regional_manager' ]);
    }

    

    /**
     * Returns the query for the table.
     */
    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();

        // Fetch visits based on user role
        if ($user->roles()->where('name', 'admin')->exists() || $user->roles()->where('name', 'accounts_head')->exists()) {
            return VisitEntry::query()->with(['user', 'trainerVisit', 'leadStatuses']);
        }

        if ($user->roles()->where('name', 'sales')->exists()) {
            return VisitEntry::query()
                ->whereHas('user', fn($query) => $query->where('company_id', $user->company_id))
                ->with(['user', 'trainerVisit', 'leadStatuses']);
        }

        $subordinateIds = $user->getAllSubordinateIds();

        return VisitEntry::query()
            ->whereIn('user_id', $subordinateIds)
            ->with(['user', 'trainerVisit', 'leadStatuses']);
    }

    /**
     * Defines the table columns.
     */
    protected function getTableColumns(): array
    {
        return [
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
                    return 'N/A'; // If start or end time is missing
                }),


            Tables\Columns\BooleanColumn::make('completed')
                ->label('Visit Completed')
                ->getStateUsing(fn($record) => !is_null($record->end_time)),

            Tables\Columns\TextColumn::make('lead_count')
                ->label('Lead Count')
                ->getStateUsing(fn($record) => $record->leadStatuses()->count()),
        ];
    }

    /**
     * Adds table actions, including a "View Leads" button.
     */
    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('view_leads')
                ->label('View Leads')
                ->url(fn($record) => route('filament.admin.resources.sales-lead-statuses.edit', ['record' => $record->id]))
                ->openUrlInNewTab()
                ->icon('heroicon-o-eye'),
        ];
    }

    /**
     * Adds filters to the table.
     */
    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\Filter::make('completed')
                ->label('Completed Visits')
                ->query(fn($query) => $query->whereNotNull('end_time')),

            Tables\Filters\Filter::make('ongoing')
                ->label('Ongoing Visits')
                ->query(fn($query) => $query->whereNull('end_time')),

            Tables\Filters\Filter::make('date_range')
                ->form([ // Add a form to define the date range
                    DatePicker::make('start_date')
                        ->label('Start Date')
                        ->default(Carbon::now()->startOfDay()),

                    DatePicker::make('end_date')
                        ->label('End Date')
                        ->default(Carbon::now()->endOfDay()),
                ])
                ->label('Date Range')
                ->query(function ($query, $data) {
                    if (!empty($data['start_date']) && !empty($data['end_date'])) {
                        $query->whereBetween('start_time', [$data['start_date'], $data['end_date']]);
                    }
                })
                ->indicateUsing(function ($data) {
                    if (!empty($data['start_date']) && !empty($data['end_date'])) {
                        return 'Date: ' . Carbon::parse($data['start_date'])->format('d-m-Y') . ' to ' . Carbon::parse($data['end_date'])->format('d-m-Y');
                    }

                    return null;
                }),
        ];
    }
}
