<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RevenewReportResource\Pages;
use App\Filament\Resources\RevenewReportResource\RelationManagers;
use App\Models\Invoice;
use App\Models\RevenewReport;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;

use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TextInputFilter;

class RevenewReportResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-rupee';

    protected static ?string $navigationLabel = 'Revenew Report';

    protected static ?string $pluralLabel = 'Revenew Report';


    protected static ?string $navigationGroup = 'Reports';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'head', 'sales_operation', 'sales_operation_head', 'zonal_manager', 'regional _manager', 'head' , 'bdm' , 'bda']);
    }



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('invoice_number')->label('Invoice #')->sortable(),
                TextColumn::make('closedBy.name')->label('Closed By'),
                TextColumn::make('school.name')
                ->label('School Name')
                ->limit(25)
                ->tooltip(fn ($record) => $record->school?->name),
            
                TextColumn::make('total_amount')->label('Total Amount')->sortable(),
                TextColumn::make('paid')->label('Paid')->sortable(),
                TextColumn::make('due_amount')->label('Due Amount')->sortable(),
                TextColumn::make('payment_status')->label('Payment Status')->badge()
            ])
            ->filters([
                // SelectFilter::make('closed_by')
                //     ->label('Closed By User')
                //     ->relationship('closedBy', 'name')
                //     ->searchable()
                //     ->indicator(fn ($state) => $state ? 'Closed By: ' . User::find($state)?->name : null),
            
                Filter::make('total_amount')
                    ->label('Total Amount')
                    ->form([
                        TextInput::make('greater_than')
                            ->label('Min Amount')
                            ->numeric()
                            ->placeholder('Enter min amount'),
                        TextInput::make('less_than')
                            ->label('Max Amount')
                            ->numeric()
                            ->placeholder('Enter max amount'),
                    ])
                    ->indicateUsing(fn ($data) => collect([
                        $data['greater_than'] ? 'Min Amount: ' . number_format($data['greater_than']) : null,
                        $data['less_than'] ? 'Max Amount: ' . number_format($data['less_than']) : null,
                    ])->filter()->join(', '))
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['greater_than'], fn ($q) => $q->where('total_amount', '>=', $data['greater_than']))
                        ->when($data['less_than'], fn ($q) => $q->where('total_amount', '<=', $data['less_than']))
                    ),
            
                Filter::make('paid')
                    ->label('Paid Amount')
                    ->form([
                        TextInput::make('greater_than')
                            ->label('Min Paid')
                            ->numeric()
                            ->placeholder('Enter min paid amount'),
                        TextInput::make('less_than')
                            ->label('Max Paid')
                            ->numeric()
                            ->placeholder('Enter max paid amount'),
                    ])
                    ->indicateUsing(fn ($data) => collect([
                        $data['greater_than'] ? 'Min: ' . number_format($data['greater_than']) : null,
                        $data['less_than'] ? 'Max: ' . number_format($data['less_than']) : null,
                    ])->filter()->join(', '))
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['greater_than'], fn ($q) => $q->where('paid', '>=', $data['greater_than']))
                        ->when($data['less_than'], fn ($q) => $q->where('paid', '<=', $data['less_than']))
                    ),
            
                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options([
                        'Paid' => 'Paid',
                        'Pending' => 'Pending',
                        'Partially Paid' => 'Partially Paid',
                    ])
                    ->indicator(fn ($state) => $state ? 'Status: ' . ucfirst($state) : null)
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
            'index' => Pages\ListRevenewReports::route('/'),
            'create' => Pages\CreateRevenewReport::route('/create'),
            'edit' => Pages\EditRevenewReport::route('/{record}/edit'),
        ];
    }
}
