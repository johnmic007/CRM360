<?php

namespace App\Filament\Resources\ClosureAmountResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClosureLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'closureLogs';

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
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('amount_closed')
                ->label('Amount Closed')
                ->sortable(),

            TextColumn::make('closedBy.name')
                ->label('Closed By')
                ->sortable()
                ->searchable(),

                TextColumn::make('closed_at')
                ->label('Closed At')
                ->sortable(),            
            ]);
            
           
           
            
            
    }
}
