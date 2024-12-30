<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesLeadStatusResource\Pages;
use App\Filament\Resources\VisitEntryResource\RelationManagers\SchoolVisitRelationManager;
use App\Models\SalesLeadStatus;
use App\Models\VisitEntry;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Tables;

class SalesLeadStatusResource extends Resource
{
    protected static ?string $model = VisitEntry::class;


    protected static ?string $navigationIcon = 'heroicon-o-bars-arrow-up';



    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            
            TextInput::make('start_time')
                ->disabled(),

                TextInput::make('end_time')
                ->disabled(),
                
                
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
            Tables\Columns\TextColumn::make('travel_type'),

            Tables\Columns\TextColumn::make('travel_expense'),

            

            Tables\Columns\TextColumn::make('user.name'),

          
            
        ]);
    }

    public static function getRelations(): array
    {
        return [
            SchoolVisitRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesLeadStatuses::route('/'),
            'create' => Pages\CreateSalesLeadStatus::route('/create'),
            'edit' => Pages\EditSalesLeadStatus::route('/{record}/edit'),
        ];
    }
}
