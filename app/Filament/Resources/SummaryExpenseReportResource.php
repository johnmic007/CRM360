<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use App\Models\TrainerVisit;
use App\Filament\Resources\SummaryExpenseReportResource\Pages;

class SummaryExpenseReportResource extends Resource
{
    protected static ?string $model = TrainerVisit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Summary Expense Report';
    protected static ?string $navigationGroup = 'Reports';

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn (Builder $query) =>
                TrainerVisit::query()
                    ->selectRaw('
                        MIN(id) as id,
                        user_id,
                        COUNT(id) as total_requests,
                        SUM(COALESCE(total_expense, 0)) as total_expense,
                        SUM(COALESCE(travel_expense, 0)) as total_travel_expense,
                        SUM(COALESCE(food_expense, 0)) as total_food_expense,
                        SUM(CASE WHEN verify_status = "verified" THEN COALESCE(total_expense, 0) ELSE 0 END) as verified_expense,
                        SUM(CASE WHEN approval_status = "approved" THEN COALESCE(total_expense, 0) ELSE 0 END) as approved_expense,
                        SUM(CASE WHEN travel_type = "extra_expense" THEN COALESCE(total_expense, 0) ELSE 0 END) as total_extra_expense,
                        (SUM(COALESCE(total_expense, 0)) / NULLIF(COUNT(id), 0)) as average_expense
                    ')
                    ->groupBy('user_id')
                    ->orderBy('id', 'asc') // Ordering by the aggregated ID
                    ->with('user')
            )
            ->filters([
                // Start Date Filter
                Filter::make('start_date')
                    ->label('Start Date')
                    ->form([
                        DatePicker::make('start_date')
                            ->placeholder('Select a start date'),
                    ])
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['start_date'] ?? null, fn ($q) =>
                            $q->whereDate('trainer_visits.created_at', '>=', $data['start_date'])
                        )
                    )
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
                        DatePicker::make('end_date')
                            ->placeholder('Select an end date'),
                    ])
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['end_date'] ?? null, fn ($q) =>
                            $q->whereDate('trainer_visits.created_at', '<=', $data['end_date'])
                        )
                    )
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
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['approval_status'] ?? null, fn ($q) =>
                            $q->where('approval_status', $data['approval_status'])
                        )
                    )
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
                    ->query(fn (Builder $query, array $data) =>
                        $query->when($data['verify_status'] ?? null, fn ($q) =>
                            $q->where('verify_status', $data['verify_status'])
                        )
                    )
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['verify_status']) {
                            return null;
                        }

                        return 'Verify Status: ' . $data['verify_status'];
                    }),

                // Exclude Users Filter
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
                        if (!empty($data['exclude_users'])) {
                            $query->whereNotIn('user_id', $data['exclude_users']);
                        }
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['exclude_users'])) {
                            return null;
                        }

                        $userNames = User::whereIn('id', $data['exclude_users'])->pluck('name')->implode(', ');

                        return 'Excluded Users: ' . $userNames;
                    }),

                // Include Users Filter
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
                        if (!empty($data['include_users'])) {
                            $query->whereIn('user_id', $data['include_users']);
                        }
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['include_users'])) {
                            return null;
                        }

                        $userNames = User::whereIn('id', $data['include_users'])->pluck('name')->implode(', ');

                        return 'Included Users: ' . $userNames;
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.wallet_balance')
                    ->label('Cash in Hand')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ₹'),

                Tables\Columns\TextColumn::make('total_requests')
                    ->label('Total Requests')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_expense')
                    ->label('Total Expense')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ₹'),

                Tables\Columns\TextColumn::make('total_travel_expense')
                    ->label('Total Travel Expense')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ₹'),

                Tables\Columns\TextColumn::make('total_food_expense')
                    ->label('Total Food Expense')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ₹'),

                Tables\Columns\TextColumn::make('total_extra_expense')
                    ->label('Total Extra Expense')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ₹'),

                Tables\Columns\TextColumn::make('verified_expense')
                    ->label('Verified Expense')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ₹'),

                Tables\Columns\TextColumn::make('approved_expense')
                    ->label('Approved Expense')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . ' ₹'),

                Tables\Columns\TextColumn::make('average_expense')
                    ->label('Average Expense')
                    ->sortable()
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
