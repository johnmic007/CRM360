<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlockResource\Pages;
use App\Models\Block;
use App\Models\District;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class BlockResource extends Resource
{
    protected static ?string $model = Block::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Utilities';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin' ,'sales_operation' , 'company' ]);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasRole(['admin' ,'sales_operation'  ]);

        
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('district_id')
                ->label('District')
                ->options(District::all()->pluck('name', 'id')) // Fetch district names for the dropdown
                ->required()
                ->searchable()
                ->placeholder('Select a district'),

            Forms\Components\TextInput::make('name')
                ->label('Block Name')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
                Tables\Columns\TextColumn::make('district.name')->label('District')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Block Name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Created At')->dateTime(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlocks::route('/'),
            'create' => Pages\CreateBlock::route('/create'),
            'edit' => Pages\EditBlock::route('/{record}/edit'),
        ];
    }
}
