<?php

namespace App\Filament\Resources\SchoolResource\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Form;

use Illuminate\Database\Eloquent\Builder;

class   SchoolPaymentRelationManager extends RelationManager
{
    protected static string $relationship = 'schoolPayments'; // Matches the School model relationship


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('type')
                    ->label('Type')
                    ->disabled(),

                TextInput::make('paid_amount')
                    ->label('Amount Paid')
                    ->disabled(),

                TextInput::make('payment_method')
                    ->label('Payment Method')
                    ->disabled(),

                 TextInput::make('reference_number')
                    ->label('Reference Number')
                    ->disabled(),

                DatePicker::make('payment_date')
                    ->label('Payment Date')
                    ->disabled(),

                Textarea::make('description')
                    ->label('Description')
                    ->disabled(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('logs')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Amount Paid')
                    ->money('inr ') // Adjust currency as needed
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Reference Number'),
                
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date()
                    ->sortable(),
                
            ])
            ->filters([
                // Additional filters can go here if needed
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),


            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->query(function (Builder $query) {
                // Apply the filter here to only include 'payment' type entries
                $query->where('type', 'payment');
            });
    }
}
