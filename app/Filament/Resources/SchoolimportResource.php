<?php

namespace App\Filament\Resources;

use App\Filament\Imports\SchoolImporter;
use App\Filament\Resources\SchoolimportResource\Pages;
use App\Filament\Resources\SchoolimportResource\RelationManagers;
use App\Models\School;
use App\Models\Schoolimport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SchoolimportResource extends Resource
{
    protected static ?string $model = School::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['old']);
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                ->label('ID')
                ->sortable(),

            Tables\Columns\TextColumn::make('block.name')
                ->label('Block')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('name')
                ->label('School Name')
                ->sortable()
                ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(SchoolImporter::class),
            ]);
          
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolimports::route('/'),
            'create' => Pages\CreateSchoolimport::route('/create'),
            'edit' => Pages\EditSchoolimport::route('/{record}/edit'),
        ];
    }
}
