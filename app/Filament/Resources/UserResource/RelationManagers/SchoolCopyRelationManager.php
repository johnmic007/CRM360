<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SchoolCopyRelationManager extends RelationManager
{
    protected static string $relationship = 'SchoolCopy';


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('book.title')->label('Book Title'), 

                Tables\Columns\TextColumn::make('school.name')->label('School Name'), 

                Tables\Columns\TextColumn::make('action')
                    ->label('Action'),
                    Tables\Columns\TextColumn::make('count')->label('Book count'), 

                    
                Tables\Columns\TextColumn::make('count')->label('Quantity'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label(' Date'),

            ])
            ->filters([
                //
            ]);
        
    }
}
