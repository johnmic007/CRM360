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
use App\Models\TrainerVisit; // Ensure this import exists
use App\Filament\Resources\SummaryExpenseReportResource\Pages;

class SummaryExpenseReportResource extends Resource
{
    protected static ?string $model = TrainerVisit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Summary Expense Report';
    protected static ?string $navigationGroup = 'Reports';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'head', 'sales_operation_head']);
    }

    public static function table(Table $table): Table
{
    return $table
    ->query(fn (Builder $query) =>
            TrainerVisit::query() // Ensures a model instance is returned
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
                ->with('user')
        )
        ->filters([
            Filter::make('start_date')
                ->label('Start Date')
                ->form([
                    DatePicker::make('start_date')
                        ->default(now()->subMonth()) // Default to one month before today
                        ->native(false),
                ])
                ->query(function (Builder $query, array $data) {
                    if (!empty($data['start_date'])) {
                        $query->whereDate('created_at', '>=', $data['start_date']);
                    }
                }),

                Filter::make('end_date')
                ->label('End Date')
                ->form([
                    DatePicker::make('end_date')
                        ->native(false),
                ])
                ->query(function (Builder $query, array $data) {
                    if (!empty($data['end_date'])) {
                        $query->whereDate('created_at', '<=', $data['end_date']);
                    }
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
                            ->placeholder('All') // Default: No filtering
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['approval_status'])) {
                            $query->where('approval_status', $data['approval_status']);
                        }
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
                            ->placeholder('All') // Default: No filtering
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['verify_status'])) {
                            $query->where('verify_status', $data['verify_status']);
                        }
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
        ])
        ->actions([
            Tables\Actions\ViewAction::make(),
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
            'create' => Pages\CreateSummaryExpenseReport::route('/create'),
            'edit' => Pages\EditSummaryExpenseReport::route('/{record}/edit'),
        ];
    }
}
