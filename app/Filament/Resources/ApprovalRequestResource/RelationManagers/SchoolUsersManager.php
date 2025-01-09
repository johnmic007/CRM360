<?php

namespace App\Filament\Resources\ApprovalRequestResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SchoolUsersManager extends RelationManager
{
    protected static string $relationship = 'schoolUsers';

   

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('user.name')
                ->label('User Name')
                ->sortable()
                ->searchable(),

            TextColumn::make('user.email')
                ->label('Email')
                ->sortable()
                ->searchable(),

            TextColumn::make('created_at')
                ->label('Added At')
                ->dateTime()
                ->sortable(),            ])
            ->filters([
                //
            ]);
           
          
    }
}
