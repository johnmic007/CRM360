<?php

namespace App\Filament\Resources\SchoolResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BookRelationManager extends RelationManager
{
    /**
     * The related model's relationship name on the parent model.
     */
    protected static string $relationship = 'books';

    /**
     * The attribute used for record titles.
     */
    protected static ?string $recordTitleAttribute = 'title';

    /**
     * Define the form schema for assigning an existing book.
     */
    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('book_id')
                ->label('Book')
                ->options(\App\Models\Book::pluck('title', 'id')) // Fetches all books
                ->searchable()
                ->required(),

            TextInput::make('books_count')
                ->label('Total Number of Books')
                ->numeric()
                ->minValue(1)
                ->required(),

            TextInput::make('issued_books_count')
                ->label('Number of Books Issued')
                ->numeric()
                ->minValue(0)
                ->required()
                ->helperText('Cannot exceed total number of books.'),
        ]);
    }

    /**
     * Define the table schema for displaying related records.
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->sortable()
                    ->searchable(),


                TextColumn::make('books_count')
                    ->label('Total Books')
                    ->sortable(),

                TextColumn::make('issued_books_count') // Access issued_books_count from pivot
                    ->label('Books Issued')
                    ->sortable(),

                TextColumn::make('remaining_books') // Virtual column for remaining books
                    ->label('Remaining Books')
                    ->getStateUsing(function ($record) {
                        return $record->books_count - $record->issued_books_count;
                    }),
            ])
            // ->headerActions([
            //     Tables\Actions\CreateAction::make()
            //         ->label('Assign Book')
            //         ->action(function (array $data) {
            //             // Validate issued_books_count does not exceed books_count
            //             if ($data['issued_books_count'] > $data['books_count']) {
            //                 throw new \Exception('Issued books cannot exceed total books.');
            //             }

            //             // Attach the book with books_count and issued_books_count
            //             $this->ownerRecord->books()->attach($data['book_id'], [
            //                 'books_count' => $data['books_count'],
            //                 'issued_books_count' => $data['issued_books_count'],
            //             ]);
            //         }),
            // ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Update Counts')
                    ->form(fn () => [
                        TextInput::make('books_count')
                            ->label('Total Books')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        TextInput::make('issued_books_count')
                            ->label('Books Issued')
                            ->numeric()
                            ->minValue(0)   
                            ->required()
                            ->helperText('Cannot exceed total books.'),
                    ])
                    ->action(function ($record, array $data) {
                        // Validate issued_books_count does not exceed books_count
                        if ($data['issued_books_count'] > $data['books_count']) {
                            throw new \Exception('Issued books cannot exceed total books.');
                        }

                        // Update the counts in the pivot table
                        $this->ownerRecord->books()->updateExistingPivot($record->id, [
                            'books_count' => $data['books_count'],
                            'issued_books_count' => $data['issued_books_count'],
                        ]);
                    }),

                // Tables\Actions\DeleteAction::make()
                //     ->label('Unassign Book')
                //     ->action(function ($record) {
                //         $this->ownerRecord->books()->detach($record->id);
                //     }),
            ])
            ->bulkActions([]);
    }
}
