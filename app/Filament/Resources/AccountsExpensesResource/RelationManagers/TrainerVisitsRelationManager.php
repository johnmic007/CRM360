<?php

namespace App\Filament\Resources\AccountsExpensesResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TrainerVisitsRelationManager extends RelationManager
{
    protected static string $relationship = 'trainerVisits';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('visit_date')
                ->label('Visit Date')
                ->date()
                ->sortable(),

            TextColumn::make('travel_mode')
                ->label('Travel Mode'),

            TextColumn::make('total_expense')
                ->label('Total Expense')
                ->sortable(),     

            TextColumn::make('approval_and_verification_status')
                ->label('Approval & Verification Status')
                ->badge() // Adds badge styling
                ->getStateUsing(function ($record) {
                    $approvalStatus = ucfirst($record->approval_status); // Capitalize the first letter
                    $verifyStatus = ucfirst($record->verify_status);     // Capitalize the first letter
                    return "{$approvalStatus} / {$verifyStatus}";
                })
                ->colors([
                    'danger' => fn($state) => str_contains($state, 'Rejected') || str_contains($state, 'Pending'),
                    'success' => fn($state) => str_contains($state, 'Approved') && str_contains($state, 'Verified'),
                    'warning' => fn($state) => str_contains($state, 'Clarification'),
                    'primary' => fn($state) => str_contains($state, 'Pending'),
                ])
                ->sortable(),
                
                ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
          
    }
}
