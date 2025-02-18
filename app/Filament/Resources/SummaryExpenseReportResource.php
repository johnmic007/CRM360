<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Models\TrainerVisit;
use Filament\Tables;
use Filament\Forms;
use Carbon\Carbon;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\SummaryExpenseReportResource\Pages;
use Filament\Tables\Columns\TextColumn;

class SummaryExpenseReportResource extends Resource
{
    /**
     * Use the User model now (instead of TrainerVisit).
     */
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationLabel = 'Summary Expense Report';

    protected static ?string $pluralLabel = 'Summary Expense Report';

    protected static ?string $navigationGroup = 'Reports';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'sales_head', 'head', 'sales_operation', 'sales_operation_head', 'zonal_manager', 'regional_manager', 'head' , 'bdm' , 'bda']);
    }


    public static function table(Table $table): Table
    {
        return $table
            

            /**
             * 2) FILTERS
             */
            ->filters([
                // Start Date Filter
                Filter::make('start_date')
                    ->label('Start Date')
                    ->form([
                        DatePicker::make('start_date')->placeholder('Select a start date'),
                    ])
                    // We won't attach a ->query() here because
                    // we’ll apply the date filter inside each column’s TrainerVisit query
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['start_date']) {
                            return null;
                        }
                        return 'Start Date: ' . $data['start_date'];
                    }),

                // End Date Filter
                Filter::make('end_date')
                    ->label('End Date')
                    ->form([
                        DatePicker::make('end_date')->placeholder('Select an end date'),
                    ])
                    // Same logic: no direct table query on the users,
                    // because the date filter is for trainer_visits
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['end_date']) {
                            return null;
                        }
                        return 'End Date: ' . $data['end_date'];
                    }),

                // Approval Status Filter
                Filter::make('approval_status')
                    ->label('Approval Status')
                    ->form([
                        Select::make('approval_status')
                            ->options([
                                'approved' => 'Approved',
                                'pending' => 'Pending',
                                'rejected' => 'Rejected',
                            ])
                            ->placeholder('All'),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['approval_status']) {
                            return null;
                        }
                        return 'Approval Status: ' . $data['approval_status'];
                    }),

                // Verify Status Filter
                Filter::make('verify_status')
                    ->label('Verify Status')
                    ->form([
                        Select::make('verify_status')
                            ->options([
                                'verified' => 'Verified',
                                'unverified' => 'Unverified',
                            ])
                            ->placeholder('All'),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['verify_status']) {
                            return null;
                        }
                        return 'Verify Status: ' . $data['verify_status'];
                    }),

                // Exclude Users Filter (applied at the user level)
                Filter::make('exclude_users')
                    ->label('Exclude Selected Users')
                    ->form([
                        Select::make('exclude_users')
                            ->multiple()
                            ->options(User::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->placeholder('Select users to exclude'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (! empty($data['exclude_users'])) {
                            // Exclude these user IDs from listing
                            $query->whereNotIn('id', $data['exclude_users']);
                        }
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['exclude_users'])) {
                            return null;
                        }
                        $userNames = User::whereIn('id', $data['exclude_users'])
                            ->pluck('name')
                            ->implode(', ');
                        return 'Excluded Users: ' . $userNames;
                    }),

                // Include Users Filter (applied at the user level)
                Filter::make('include_users')
                    ->label('Include Selected Users')
                    ->form([
                        Select::make('include_users')
                            ->multiple()
                            ->options(User::pluck('name', 'id')->toArray())
                            ->searchable()
                            ->placeholder('Select users to include'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (! empty($data['include_users'])) {
                            // Show ONLY these user IDs
                            $query->whereIn('id', $data['include_users']);
                        }
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['include_users'])) {
                            return null;
                        }
                        $userNames = User::whereIn('id', $data['include_users'])
                            ->pluck('name')
                            ->implode(', ');
                        return 'Included Users: ' . $userNames;
                    }),
            ])

            /**
             * 3) COLUMNS
             *
             * Each column uses `->getStateUsing(...)` to query the trainer_visits table
             * for that user, applying the relevant filters from $livewire->tableFilters.
             */
            ->columns([
                // Basic user info:
                Tables\Columns\TextColumn::make('name')
                    ->label('User Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('wallet_balance')
                    ->label('Cash in Hand')
                    ->sortable()
                    ->placeholder('0')
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ₹'),

                // -------------- AGGREGATE COLUMNS --------------
                Tables\Columns\TextColumn::make('total_requests')
                    ->label('Total Requests')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;

                        // Base query for this user
                        $query = TrainerVisit::where('user_id', $record->id);

                        // Apply date filters
                        if (! empty($filters['start_date']['start_date'])) {
                            $query->whereDate('visit_date', '>=', $filters['start_date']['start_date']);
                        }
                        if (! empty($filters['end_date']['end_date'])) {
                            $query->whereDate('visit_date', '<=', $filters['end_date']['end_date']);
                        }

                        // Apply approval status
                        if (! empty($filters['approval_status']['approval_status'])) {
                            $query->where('approval_status', $filters['approval_status']['approval_status']);
                        }

                        // Apply verify status
                        if (! empty($filters['verify_status']['verify_status'])) {
                            $query->where('verify_status', $filters['verify_status']['verify_status']);
                        }

                        return $query->count();
                    }),

                Tables\Columns\TextColumn::make('total_expense')
                    ->label('Total Expense')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $query = TrainerVisit::where('user_id', $record->id);

                        // Apply filters (same pattern)
                        if (! empty($filters['start_date']['start_date'])) {
                            $query->whereDate('visit_date', '>=', $filters['start_date']['start_date']);
                        }
                        if (! empty($filters['end_date']['end_date'])) {
                            $query->whereDate('visit_date', '<=', $filters['end_date']['end_date']);
                        }
                        if (! empty($filters['approval_status']['approval_status'])) {
                            $query->where('approval_status', $filters['approval_status']['approval_status']);
                        }
                        if (! empty($filters['verify_status']['verify_status'])) {
                            $query->where('verify_status', $filters['verify_status']['verify_status']);
                        }

                        return $query->sum('total_expense');
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ₹'),

                Tables\Columns\TextColumn::make('total_travel_expense')
                    ->label('Total Travel Expense')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $query = TrainerVisit::where('user_id', $record->id);

                        // Apply filters
                        if (! empty($filters['start_date']['start_date'])) {
                            $query->whereDate('visit_date', '>=', $filters['start_date']['start_date']);
                        }
                        if (! empty($filters['end_date']['end_date'])) {
                            $query->whereDate('visit_date', '<=', $filters['end_date']['end_date']);
                        }
                        if (! empty($filters['approval_status']['approval_status'])) {
                            $query->where('approval_status', $filters['approval_status']['approval_status']);
                        }
                        if (! empty($filters['verify_status']['verify_status'])) {
                            $query->where('verify_status', $filters['verify_status']['verify_status']);
                        }

                        return $query->sum('travel_expense');
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ₹'),

                Tables\Columns\TextColumn::make('total_food_expense')
                    ->label('Total Food Expense')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $query = TrainerVisit::where('user_id', $record->id);

                        // Apply filters...
                        if (! empty($filters['start_date']['start_date'])) {
                            $query->whereDate('visit_date', '>=', $filters['start_date']['start_date']);
                        }
                        if (! empty($filters['end_date']['end_date'])) {
                            $query->whereDate('visit_date', '<=', $filters['end_date']['end_date']);
                        }
                        if (! empty($filters['approval_status']['approval_status'])) {
                            $query->where('approval_status', $filters['approval_status']['approval_status']);
                        }
                        if (! empty($filters['verify_status']['verify_status'])) {
                            $query->where('verify_status', $filters['verify_status']['verify_status']);
                        }

                        return $query->sum('food_expense');
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ₹'),

                Tables\Columns\TextColumn::make('total_extra_expense')
                    ->label('Total Extra Expense')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $query = TrainerVisit::where('user_id', $record->id);

                        // Apply filters...
                        if (! empty($filters['start_date']['start_date'])) {
                            $query->whereDate('visit_date', '>=', $filters['start_date']['start_date']);
                        }
                        if (! empty($filters['end_date']['end_date'])) {
                            $query->whereDate('visit_date', '<=', $filters['end_date']['end_date']);
                        }
                        if (! empty($filters['approval_status']['approval_status'])) {
                            $query->where('approval_status', $filters['approval_status']['approval_status']);
                        }
                        if (! empty($filters['verify_status']['verify_status'])) {
                            $query->where('verify_status', $filters['verify_status']['verify_status']);
                        }

                        // 'extra_expense' often indicated by travel_type = "extra_expense"
                        return $query->where('travel_type', 'extra_expense')->sum('total_expense');
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ₹'),

                Tables\Columns\TextColumn::make('verified_expense')
                    ->label('Verified Expense')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $query = TrainerVisit::where('user_id', $record->id);

                        // Apply date/approval filters...
                        if (! empty($filters['start_date']['start_date'])) {
                            $query->whereDate('visit_date', '>=', $filters['start_date']['start_date']);
                        }
                        if (! empty($filters['end_date']['end_date'])) {
                            $query->whereDate('visit_date', '<=', $filters['end_date']['end_date']);
                        }
                        if (! empty($filters['approval_status']['approval_status'])) {
                            $query->where('approval_status', $filters['approval_status']['approval_status']);
                        }
                        // We specifically want "verified" here:
                        $query->where('verify_status', 'verified');

                        return $query->sum('total_expense');
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ₹'),

                Tables\Columns\TextColumn::make('approved_expense')
                    ->label('Approved Expense')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $query = TrainerVisit::where('user_id', $record->id);

                        // Apply date/verify filters...
                        if (! empty($filters['start_date']['start_date'])) {
                            $query->whereDate('visit_date', '>=', $filters['start_date']['start_date']);
                        }
                        if (! empty($filters['end_date']['end_date'])) {
                            $query->whereDate('visit_date', '<=', $filters['end_date']['end_date']);
                        }
                        if (! empty($filters['verify_status']['verify_status'])) {
                            $query->where('verify_status', $filters['verify_status']['verify_status']);
                        }
                        // We specifically want "approved" here:
                        $query->where('approval_status', 'approved');

                        return $query->sum('total_expense');
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ₹'),

                Tables\Columns\TextColumn::make('average_expense')
                    ->label('Average Expense')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $query = TrainerVisit::where('user_id', $record->id);

                        // Apply date/approval/verify filters...
                        if (! empty($filters['start_date']['start_date'])) {
                            $query->whereDate('visit_date', '>=', $filters['start_date']['start_date']);
                        }
                        if (! empty($filters['end_date']['end_date'])) {
                            $query->whereDate('visit_date', '<=', $filters['end_date']['end_date']);
                        }
                        if (! empty($filters['approval_status']['approval_status'])) {
                            $query->where('approval_status', $filters['approval_status']['approval_status']);
                        }
                        if (! empty($filters['verify_status']['verify_status'])) {
                            $query->where('verify_status', $filters['verify_status']['verify_status']);
                        }

                        $avg = $query->avg('total_expense') ?? 0;
                        return $avg;
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ₹'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSummaryExpenseReports::route('/'),
        ];
    }
}
