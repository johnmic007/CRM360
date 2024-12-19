<?php

namespace App\Filament\Resources\InvoiceLogResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceLogRelation extends RelationManager
{
    protected static string $relationship = 'logs'; // Defined in the Invoice model

    protected static ?string $recordTitleAttribute = 'type';


    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('type')
                ->label('Type')
                ->disabled() // Logs should not be edited directly
                ->required(),

            Forms\Components\Textarea::make('description')
                ->label('Description')
                ->disabled(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('type')
                ->label('Type')
                ->sortable()
                ->searchable(),

            Tables\Columns\TextColumn::make('description')
                ->label('Description')
                ->wrap(),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Logged At')
                ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
