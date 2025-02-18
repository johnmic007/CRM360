<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentReportResource\Pages;
use App\Filament\Resources\PaymentReportResource\RelationManagers;
use App\Models\InvoiceLog;
use App\Models\PaymentReport;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use Filament\Tables\Filters\SelectFilter;


class PaymentReportResource extends Resource
{
    protected static ?string $model = InvoiceLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Payment Report';

    protected static ?string $pluralLabel = 'Payment Report';

    protected static ?string $navigationGroup = 'Reports';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'sales_head', 'head', 'sales_operation', 'sales_operation_head', 'zonal_manager', 'regional_manager', 'head' , 'bdm' , 'bda']);
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
                TextColumn::make('paid_amount')
                ->label('Paid Amount')
                ->sortable()
                ->money('INR'), // Format as currency (optional)

            TextColumn::make('invoice.closedBy.name')
                ->label('Closed By')
                ->searchable(),

                TextColumn::make('invoice.school.name')
                ->label('School Name')
                ->searchable(),
            ])
           
            
    
            
            ->filters([
                // Filter for paid amount greater than
                Filter::make('paid_greater')
                    ->form([
                        TextInput::make('amount')
                            ->label('Paid Greater Than')
                            ->numeric()
                            ->placeholder('Enter amount'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return isset($data['amount']) && is_numeric($data['amount'])
                            ? $query->where('paid_amount', '>=', $data['amount'])
                            : $query;
                    })
                    ->indicateUsing(function (array $data): ?string {
                        return isset($data['amount']) && is_numeric($data['amount'])
                            ? 'Paid ≥ ' . number_format($data['amount'], 2) . ' INR'
                            : null;
                    }),
            
                // Filter for paid amount less than
                Filter::make('paid_less')
                    ->form([
                        TextInput::make('amount')
                            ->label('Paid Less Than')
                            ->numeric()
                            ->placeholder('Enter amount'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return isset($data['amount']) && is_numeric($data['amount'])
                            ? $query->where('paid_amount', '<=', $data['amount'])
                            : $query;
                    })
                    ->indicateUsing(function (array $data): ?string {
                        return isset($data['amount']) && is_numeric($data['amount'])
                            ? 'Paid ≤ ' . number_format($data['amount'], 2) . ' INR'
                            : null;
                    }),
            
                // Filter by Closed By (User)
                // SelectFilter::make('closed_by')
                // ->label('Closed By')
                // ->options(fn () => User::pluck('name', 'id')->toArray()) // Fetch user list correctly
                // ->searchable()
                // ->query(function (Builder $query, $value): Builder {
                //     return $value
                //         ? $query->whereHas('invoice', fn ($q) => $q->where('closed_by', $value)) // Correct filter
                //         : $query;
                // })
                // ->indicator('Closed By') // Ensures an active indicator is shown
                // ->indicateUsing(function ($state) {
                //     if (!$state) return null;
                    
                //     $user = User::find($state); // Ensure a single instance is retrieved
                //     return $user ? 'Closed By: ' . $user->name : 'Closed By: Unknown User';
                // }),            
            ])
            
            
            ->actions([
                // Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListPaymentReports::route('/'),
            'create' => Pages\CreatePaymentReport::route('/create'),
            'edit' => Pages\EditPaymentReport::route('/{record}/edit'),
        ];
    }
}
