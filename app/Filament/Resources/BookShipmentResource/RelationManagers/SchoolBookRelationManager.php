<?php

namespace App\Filament\Resources\BookShipmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;

class SchoolBookRelationManager extends RelationManager
{
    protected static string $relationship = 'schoolBook'; // Ensure it matches the relationship method in School model

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('book_id')
                    ->label('Book')
                    ->relationship('book', 'title')
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('books_count')
                    ->label('Books Count')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('price')
                    ->label('Price per Book')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('total')
                    ->label('Total Price')
                    ->numeric()
                    ->required(),

                    TextColumn::make('created_at')
                    ->label('Date Issued')
                    ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('d M Y')) // Format to Day Month Year
                    ->sortable(),
                
                
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('book.title')
                    ->label('Book Name')
                    ->sortable()
                    ->searchable(),

                    TextColumn::make('invoice.invoice_number') // Fetch Invoice Number
                    ->label('Invoice Number')
                    ->sortable()
                    ->searchable()
                    ->placeholder('--'),

                TextColumn::make('books_count')
                    ->label('Books Count')
                    ->sortable(),

                // TextColumn::make('price')
                //     ->label('Price per Book')
                //     ->money('INR')
                //     ->sortable(),

                // TextColumn::make('total')
                //     ->label('Total Price')
                //     ->money('INR')
                //     ->sortable(),

                TextColumn::make('issued_books_count')
                    ->label('Issued Books Count')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date Issued')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([]);
            // ->headerActions([
            //     CreateAction::make(),
            // ])
            // ->actions([
                //     EditAction::make(),
            //     DeleteAction::make(),
            // ])
            
    }
}
