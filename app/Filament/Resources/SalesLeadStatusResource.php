<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesLeadStatusResource\Pages;
use App\Filament\Resources\VisitEntryResource\RelationManagers\SchoolVisitRelationManager;
use App\Models\SalesLeadStatus;
use App\Models\VisitEntry;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;

class SalesLeadStatusResource extends Resource
{
    protected static ?string $model = VisitEntry::class;


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            
            
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
            Tables\Columns\TextColumn::make('visitEntry.id')->label('Visit Entry ID'),
            Tables\Columns\TextColumn::make('sales_lead_management_id')->label('Lead Management ID'),
            Tables\Columns\TextColumn::make('status'),
            Tables\Columns\TextColumn::make('remarks'),
            Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime(),
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
