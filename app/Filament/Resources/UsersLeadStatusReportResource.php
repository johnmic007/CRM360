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
use App\Models\SalesLeadStatus;
use App\Filament\Resources\SalesLeadStatusReportResource\Pages;
use App\Filament\Resources\UsersLeadStatusReportResource\Pages\ListUsersLeadStatusReports;

class UsersLeadStatusReportResource extends Resource
{
    protected static ?string $model = SalesLeadStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'users Lead Status Report';
    protected static ?string $navigationGroup = 'Reports';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'head', 'sales_manager']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn (Builder $query) =>
                SalesLeadStatus::query()
                    ->selectRaw('
                        MIN(id) as id,
                        created_by as user_id,
                        COUNT(id) as total_visits,
                        SUM(CASE WHEN is_book_issued = 1 THEN 1 ELSE 0 END) as total_books_issued,
                        SUM(CASE WHEN status = "follow_up" THEN 1 ELSE 0 END) as total_follow_ups,
                        SUM(CASE WHEN status = "closed" THEN 1 ELSE 0 END) as total_closed_leads,
                        SUM(CASE WHEN status = "School Nurturing" THEN 1 ELSE 0 END) as total_school_nurturing,
                        SUM(CASE WHEN status = "Demo Reschedule" THEN 1 ELSE 0 END) as total_demo_reschedule,
                        SUM(CASE WHEN status = "Demo Completed" THEN 1 ELSE 0 END) as total_demo_completed,
                        SUM(CASE WHEN status = "deal_won" THEN 1 ELSE 0 END) as total_deal_won,
                        SUM(CASE WHEN status = "deal_lost" THEN 1 ELSE 0 END) as total_deal_lost
                    ')
                    ->groupBy('created_by')
                    ->with('user')
            )
            ->filters([
                Filter::make('start_date')
                    ->label('Start Date')
                    ->form([
                        DatePicker::make('start_date')
                            ->default(now()->subMonth())
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['start_date'])) {
                            $query->whereDate('visited_date', '>=', $data['start_date']);
                        }
                    }),

                Filter::make('end_date')
                    ->label('End Date')
                    ->form([
                        DatePicker::make('end_date')->native(false),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['end_date'])) {
                            $query->whereDate('visited_date', '<=', $data['end_date']);
                        }
                    }),

                Filter::make('status')
                    ->label('Lead Status')
                    ->form([
                        Select::make('status')
                            ->options([
                                'School Nurturing' => 'School Nurturing',
                                'Demo Reschedule' => 'Demo Reschedule',
                                'Demo Completed' => 'Demo Completed',
                                'deal_won' => 'Deal Won',
                                'deal_lost' => 'Deal Lost',
                            ])
                            ->placeholder('All')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['status'])) {
                            $query->where('status', $data['status']);
                        }
                    }),

                Filter::make('created_by')
                    ->label('Created By')
                    ->form([
                        Select::make('created_by')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->placeholder('All'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['created_by'])) {
                            $query->where('created_by', $data['created_by']);
                        }
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Created By')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_visits')
                    ->label('Total Visits')
                    ->sortable(),

                // Tables\Columns\TextColumn::make('total_books_issued')
                //     ->label('Books Issued')
                //     ->sortable(),

                // Tables\Columns\TextColumn::make('total_follow_ups')
                //     ->label('Follow Ups')
                //     ->sortable(),

                // Tables\Columns\TextColumn::make('total_closed_leads')
                //     ->label('Closed Leads')
                //     ->sortable(),

                Tables\Columns\TextColumn::make('total_school_nurturing')
                    ->label('School Nurturing')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_demo_reschedule')
                    ->label('Demo Schedule')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_demo_completed')
                    ->label('Demo Completed')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_deal_won')
                    ->label('Deal Won')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_deal_lost')
                    ->label('Deal Lost')
                    ->sortable(),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }



    public static function getPages(): array
    {
        return [
            'index' => ListUsersLeadStatusReports::route('/'),
        ];
    }
}
