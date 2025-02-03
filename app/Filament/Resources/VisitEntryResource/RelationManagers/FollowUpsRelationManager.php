<?php

namespace App\Filament\Resources\VisitEntryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FollowUpsRelationManager extends RelationManager
{
    protected static string $relationship = 'todaySchoolFollowups';

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
                Tables\Columns\TextColumn::make('school.name')->label('School'),
                Tables\Columns\TextColumn::make('block.name')->label('Block'),


                Tables\Columns\TextColumn::make('status')->label('Status'),
                Tables\Columns\TextColumn::make('visited_date')->label('Visited Date')->date(),
                Tables\Columns\TextColumn::make('follow_up_date')->date()
                ->dateTime('Y-m-d'),            ])
            ->filters([
                //
            ]);
            
           
    }
}
