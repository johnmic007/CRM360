<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MouResource\Pages;
use App\Filament\Resources\MouResource\RelationManagers;
use App\Models\Mou;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class MouResource extends Resource
{
    protected static ?string $model = Mou::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['old' ]);
    }



    
    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')
                ->label('Title')
                ->required()
                ->maxLength(255),
    
            Textarea::make('description')
                ->label('Description')
                ->maxLength(500)
                ->nullable(),
    
            Select::make('school_id')
                ->label('School')
                ->relationship('school', 'name') // Assumes the `School` model has a `name` attribute
                ->required(),
    
            Select::make('company_id')
                ->label('Company')
                ->relationship('company', 'name') // Assumes the `Company` model has a `name` attribute
                ->required(),
    
            FileUpload::make('image')
                ->label('Upload Image')
                ->disk('s3')
                ->visibility('public')
                ->directory('MGC_CRM')
                ->nullable(),
        ]);
    }
    


    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->sortable()
                    ->searchable(),
    
                TextColumn::make('school.name')
                    ->label('School')
                    ->sortable()
                    ->searchable(),
    
                TextColumn::make('company.name')
                    ->label('Company')
                    ->sortable()
                    ->searchable(),
    
                ImageColumn::make('image')
                    ->label('Image')
                    ->width(50)
                    ->height(50),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListMous::route('/'),
            'create' => Pages\CreateMou::route('/create'),
            'edit' => Pages\EditMou::route('/{record}/edit'),
        ];
    }
}
