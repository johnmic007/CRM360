<?php

namespace App\Filament\Resources\SchoolResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeadStatusesRelationManager extends RelationManager
{
    protected static string $relationship = 'leadStatuses';



    
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('status')->sortable()->searchable(),
                TextColumn::make('remarks')->limit(50),
                TextColumn::make('contacted_person')->label('Contacted Person'),
                TextColumn::make('contacted_person_designation')->label('Designation'),
                TextColumn::make('visited_by')->label('Visited By')->getStateUsing(fn ($record) => $record->visitedBy?->name),
                TextColumn::make('follow_up_date')->date(),
                TextColumn::make('visited_date')->date(),            ])
            ->filters([
                //
            ]);
            
    }
}
