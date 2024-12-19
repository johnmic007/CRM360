<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Filament\Resources\BookResource\RelationManagers;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Utilities';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')
                ->label('Title')
                ->required()
                ->maxLength(255),

            TextInput::make('author')
                ->label('Author')
                ->required()
                ->maxLength(255),

            TextInput::make('isbn')
                ->label('ISBN')
                ->required()
                ->unique(Book::class, 'isbn')
                ->maxLength(13),

                // TextInput::make('price')
                // ->label('Price')
                // ->required()
                // ->numeric()
                // ->prefix('₹') // Adds a dollar sign before the input
                // ->minValue(0)
                // ->placeholder('Enter the book price'),            

            TextInput::make('published_year')
                ->label('Published Year')
                ->required()
                ->numeric()
                ->minValue(1000)
                ->maxValue((int)date('Y')),

            Textarea::make('description')
                ->label('Description')
                ->maxLength(500),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                ->label('ID')
                ->sortable(),

            TextColumn::make('title')
                ->label('Title')
                ->sortable()
                ->searchable(),

            TextColumn::make('author')
                ->label('Author')
                ->sortable()
                ->searchable(),

            TextColumn::make('isbn')
                ->label('ISBN')
                ->searchable(),

            TextColumn::make('published_year')
                ->label('Published Year')
                ->sortable(),
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
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
        ];
    }
}
