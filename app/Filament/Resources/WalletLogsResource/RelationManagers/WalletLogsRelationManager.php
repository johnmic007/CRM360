<?php

namespace App\Filament\Resources\WalletLogsResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WalletLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'walletLogs';

   

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
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
                TextColumn::make('created_at')->label('Date')->dateTime(),            ])
            ->filters([
                //
            ]);
           
            
            
            
    }
}
