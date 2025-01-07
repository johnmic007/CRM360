<?php


namespace App\Filament\Resources;

use App\Filament\Resources\AccountsExpensesResource\Pages;
use App\Filament\Resources\AccountsExpensesResource\RelationManagers\TrainerVisitsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\WalletPaymentLogsRelationManager;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountsExpensesResource extends Resource
{
    protected static ?string $model = User::class; // Change to User model

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Accounts Expenses Report';

    protected static ?string $pluralLabel = 'Accounts Expenses Reports';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
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

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->roles->pluck('name')->join(', ')),

                TextColumn::make('total_amount_received')
                    ->label('Total Amount Received')
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->walletLogs()
                        ->where('type', 'credit')
                        ->sum('amount')),

                TextColumn::make('total_amount_spent')
                    ->label('Total Amount Spent')
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->walletLogs()
                        ->where('type', 'debit')
                        ->sum('amount')),

                TextColumn::make('wallet_balance')
                    ->label('Cash in Hand')
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->wallet_balance),
            ])
            ->filters([
                // Add filters if necessary
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TrainerVisitsRelationManager::class,
            WalletPaymentLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountsExpenses::route('/'),
            'create' => Pages\CreateAccountsExpenses::route('/create'),
            'edit' => Pages\EditAccountsExpenses::route('/{record}/edit'),
        ];
    }
}
