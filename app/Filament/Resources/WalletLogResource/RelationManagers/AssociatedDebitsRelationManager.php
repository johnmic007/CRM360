<?php

namespace App\Filament\Resources\WalletLogResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssociatedDebitsRelationManager extends RelationManager
{
    protected static string $relationship = 'associatedDebits';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
             
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                ->label('ID')
                ->sortable(),

            Tables\Columns\TextColumn::make('amount')
                ->label('Amount')
                ->sortable(),

            // Tables\Columns\TextColumn::make('balance')
            //     ->label('Balance')
            //     ->sortable(),

            Tables\Columns\TextColumn::make('description')
                ->label('Description'),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Created At')
                ->dateTime(),
        ])
            ->filters([
                //
            ]);
            
    }
}
