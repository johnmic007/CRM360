<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Items;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ItemsResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ItemsResource\RelationManagers;

class ItemsResource extends Resource
{
    protected static ?string $model = Items::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';


    protected static ?string $navigationGroup = 'Utilities';



    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'sales_head' , 'head' , 'sales_operation']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(255),

                TextInput::make('price')
                ->label('Price')
                ->required(),

                TextInput::make('remarks')
                ->label('Remarks')
                ->maxLength(255)
                ->nullable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('remarks') // ✅ Add Remarks Column in Table
                ->label('Remarks')
                ->wrap()
                ->sortable()
                ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItems::route('/create'),
            'edit' => Pages\EditItems::route('/{record}/edit'),
        ];
    }
}
