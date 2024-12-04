<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class WalletPaymentLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'walletPaymentLogs';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('amount')
                    ->label('Amount')
                    ->sortable()
                    ->money('INR') // Format amount as currency
                    ->alignCenter(),

                TextColumn::make('transaction_type')
                    ->label('Transaction Type')
                    ->sortable()
                    ->searchable()
                    ->alignCenter(),

                TextColumn::make('description')
                    ->label('Description')
                    ->wrap() // Wrap long descriptions
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('Y-m-d H:i:s') // Display date in a custom format
                    ->sortable()
                    ->alignCenter(),

                // IconColumn::make('status')
                //     ->label('Status')
                //     ->sortable()
                //     ->boolean() // Display a boolean value as an icon (checkmark or cross)
                //     ->trueIcon('heroicon-o-check-circle')
                //     ->falseIcon('heroicon-o-x-circle')
                //     ->alignCenter(),
            ])
            ->filters([
                // Optional filters if needed, like status or date filters
            ])
            // ->actions([
            //     Action::make('view_details')
            //         ->label('View Details')
            //         ->icon('heroicon-o-eye')
            //         ->url(fn ($record) => route('wallet-payment-logs.show', $record->id)) // Link to detailed page
            //         ->color('primary'),
            // ])
            ->bulkActions([
                // Optional bulk actions, like bulk delete
            ]);
    }
}
