<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsersPerformanceReportResource\Pages;
use App\Filament\Resources\UsersPerformanceReportResource\RelationManagers;
use App\Models\UsersPerformanceReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use App\Models\SalesLeadStatus;
use App\Filament\Resources\UsersLeadStatusReportResource\Pages\ListUsersLeadStatusReports;
use App\Models\User;
use App\Models\VisitEntry;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Log;

class UsersPerformanceReportResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Users Performance Report';

        protected static ?string $pluralLabel = 'users Performance Report';

    protected static ?string $navigationGroup = 'Reports';

    // public static function canViewAny(): bool
    // {
    //     return auth()->user()->hasRole(['admin', 'head', 'sales_operation_head', 'zonal_manager', 'regional_manager', 'head']);
    // }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),
    
                // Number of Visits in the chosen date range
                TextColumn::make('visits_in_range')
                    ->label('No. of Visits')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $startDate = data_get($filters['date_range'], 'start_date');
                        $endDate   = data_get($filters['date_range'], 'end_date');
    
                        $query = $record->visits();
    
                        if ($startDate) {
                            $query->where('visited_date', '>=', $startDate);
                        }
                        if ($endDate) {
                            $query->where('visited_date', '<=', $endDate);
                        }
    
                        return $query->count();
                    }),
    
                    TextColumn::make('potential_meet_in_range')
                    ->label('No. of Potential Meets')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $startDate = data_get($filters['date_range'], 'start_date');
                        $endDate   = data_get($filters['date_range'], 'end_date');
                
                        // Get sales lead statuses where `potential_meet` is true (1)
                        $query = $record->visits()->where('potential_meet', true);
                
                        if ($startDate) {
                            $query->whereDate('visited_date', '>=', $startDate);
                        }
                        if ($endDate) {
                            $query->whereDate('visited_date', '<=', $endDate);
                        }
                
                        return $query->count();
                    })
                    ->sortable(),
                    
                    
                    // Count of MOUs (closed Invoices) in the chosen date range
                TextColumn::make('mou_in_range')
                ->label('No. of MOUs')
                ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                    $filters = $livewire->tableFilters;
                    $startDate = data_get($filters['date_range'], 'created_at');
                    $endDate   = data_get($filters['date_range'], 'created_at');

                    $query = $record->closedInvoices();

                    if ($startDate) {
                        $query->where('issue_date', '>=', $startDate);
                    }
                    if ($endDate) {
                        $query->where('issue_date', '<=', $endDate);
                    }

                    return $query->count();
                }),

    
                    TextColumn::make('total_amount_in_range')
                    ->label('Total Amount')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $startDate = data_get($filters['date_range'], 'created_at');
                        $endDate   = data_get($filters['date_range'], 'created_at');
                
                        // Get invoices filtered by date
                        $query = $record->closedInvoices();
                
                        if ($startDate) {
                            $query->whereDate('issue_date', '>=', $startDate);
                        }
                        if ($endDate) {
                            $query->whereDate('issue_date', '<=', $endDate);
                        }
                
                        // Fetch results for debugging
                        $invoices = $query->get(['id', 'total_amount']);
                
                        // Debugging: Log invoices and their amounts
                        Log::info("User ID: {$record->id} | Invoices: " . json_encode($invoices->toArray()));
                
                        // Ensure total_amount is a valid number before summing
                        $sum = $invoices->sum('total_amount');
                
                        return $sum;
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->sortable(),    
                    
                    // or ->formatStateUsing(fn ($value) => number_format($value, 2)),


                    TextColumn::make('total_amount_in_bank')
                    ->label('Revenue In Bank')
                    ->getStateUsing(function (User $record, \Livewire\Component $livewire) {
                        $filters = $livewire->tableFilters;
                        $startDate = data_get($filters['date_range'], 'start_date');
                        $endDate   = data_get($filters['date_range'], 'end_date');
                
                        // Get invoices filtered by date
                        $query = $record->closedInvoices();
                
                        if ($startDate) {
                            $query->whereDate('issue_date', '>=', $startDate);
                        }
                        if ($endDate) {
                            $query->whereDate('issue_date', '<=', $endDate);
                        }
                
                        // Fetch results for debugging
                        $invoices = $query->get(['id', 'paid']);
                
                        // Debugging: Log invoices and their amounts
                        Log::info("User ID: {$record->id} | Invoices: " . json_encode($invoices->toArray()));
                
                        // Ensure total_amount is a valid number before summing
                        $sum = $invoices->sum('paid');
                
                        return $sum;
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2))
                    ->sortable(),
            ])
            ->filters([
                Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->closeOnDateSelection(false),
            
                        Forms\Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->closeOnDateSelection(false),
                    ])
                    ->indicateUsing(fn ($data) => 
                        ($data['start_date'] ?? null) || ($data['end_date'] ?? null)
                            ? 'From ' . ($data['start_date'] ? \Carbon\Carbon::parse($data['start_date'])->format('d M Y') : '...')
                              . ' to ' . ($data['end_date'] ? \Carbon\Carbon::parse($data['end_date'])->format('d M Y') : '...')
                            : null
                    )
                    // ->query(function ($query, array $data) {
                    //     // Apply filtering based on date range
                    //     if (!empty($data['start_date'])) {
                    //         $query->whereDate('created_at', '>=', $data['start_date']);
                    //     }
                    //     if (!empty($data['end_date'])) {
                    //         $query->whereDate('created_at', '<=', $data['end_date']);
                    //     }
            
                    //     return $query;
                    // }),

                    ->query(function ($query, array $data) {
                       
            
                        return $query;
                    }),
                ]);
            
    }
    

   
    

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsersPerformanceReports::route('/'),
            'create' => Pages\CreateUsersPerformanceReport::route('/create'),
            'edit' => Pages\EditUsersPerformanceReport::route('/{record}/edit'),
        ];
    }
}
