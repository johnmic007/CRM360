<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletLogResource\Pages;
use App\Filament\Resources\WalletLogResource\RelationManagers\AssociatedDebitsRelationManager;
use App\Filament\Widgets\WalletBalanceWidget;
use App\Models\WalletLog;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;

class WalletLogResource extends Resource
{
    protected static ?string $model = WalletLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    
    protected static ?string $navigationLabel = 'Wallet Logs';
    protected static ?string $navigationGroup = 'Finance Management';


    
    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'sales_head'  , 'bda' , 'bdm' , 'zonal_manager' , 'regional_manager' , 'head' , 'accounts_head' , 'sales_operation' , 'trainer']);
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                

            ]);
    }


    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('User'),
                TextColumn::make('amount')->label('Amount')->money('INR'),
                TextColumn::make('type')
                ->label('Type')
                ->badge() // Use badge styling
                ->colors([
                    'success' => 'credit',
                    'danger' => 'debit',
                ]),
                              TextColumn::make('description')->label('Description'),
                TextColumn::make('approver.name')->label('Approved By'),
                TextColumn::make('created_at')->label('Date')->dateTime(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),

            ])
            ->bulkActions([])
            ->paginated([10, 25,]);

    }

    public static function getRelations(): array
    {
        return [
            AssociatedDebitsRelationManager::class,

        ];
    }

 

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWalletLogs::route('/'),
            'edit' => Pages\EditWalletLog::route('/{record}/edit'),

        ];
    }
}
