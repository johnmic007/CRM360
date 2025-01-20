<?php

namespace App\Filament\Resources\CompanyAccountResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyTransactionRelationManager  extends RelationManager
{
    protected static string $relationship = 'companyTransactions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        
        return $table
            ->columns([
                TextColumn::make('transaction_id')
                    ->label('Transaction ID')
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('balance')
                    ->label('Balance')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'success' => 'credit',
                        'danger' => 'debit',
                    ]),

                TextColumn::make('performed_by')
                    ->label('Performed By')
                    ->getStateUsing(fn ($record) => $record->performedBy->name ?? 'System'),

                TextColumn::make('requested_at')
                    ->label('Requested At')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('issued_at')
                    ->label('Issued At')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->searchable(),

                ]);
          
    }
}
