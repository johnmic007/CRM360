<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WalletLogResource\Pages;
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
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getWidgets(): array
    {
        return [
            WalletBalanceWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWalletLogs::route('/'),
        ];
    }
}
