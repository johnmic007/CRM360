<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IssuedBooksRelationManager extends RelationManager
{
    protected static string $relationship = 'issuedBooks'; // Define the relationship

    public function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Select::make('book_id')
                    ->label('Book')
                    ->options(\App\Models\Book::pluck('title', 'id'))
                    ->required(),
                    TextInput::make('count')
                    ->label('Count')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->helperText('Enter the number of demo books issued.')
                    ->reactive() // Make the field reactive so changes are captured immediately
                    ->afterStateUpdated(function (callable $set, $state) {
                        // Whenever 'count' changes, update 'stock_count' accordingly
                        $set('stock_count', $state);
                    }),
                
                Hidden::make('stock_count'),
                
            ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->recordTitleAttribute('book.title') // Assuming 'book' is the related model
            ->columns([
                TextColumn::make('book.title')->label('Book Title'),
                TextColumn::make('count')->label('Count'),
                TextColumn::make('stock_count')->label('Stock Count'),

            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
