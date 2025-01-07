<?php


namespace App\Filament\Resources;

use App\Filament\Resources\AccountsExpensesResource\Pages;
use App\Filament\Resources\AccountsExpensesResource\Pages\ListAccountsExpenses;
use App\Filament\Resources\AccountsExpensesResource\Pages\ViewAccountsExpenses;
use App\Filament\Resources\AccountsExpensesResource\RelationManagers\TrainerVisitsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\WalletPaymentLogsRelationManager;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountsExpensesResource extends Resource
{
    protected static ?string $model = User::class; // Change to User model

    protected static ?string $navigationIcon = 'heroicon-o-document-currency-rupee';

    protected static ?string $navigationLabel = 'Accounts Expenses Report';

    protected static ?string $pluralLabel = 'Accounts Expenses Reports';

    protected static ?string $navigationGroup = 'Reports';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin' , 'sales_operation_head' , 'accounts_head']);
    }



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
            
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


                    TextColumn::make('last_expense')
                    ->label('Last Expense')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        $lastExpense = $record->trainerVisits()->latest('visit_date')->first();
                        return $lastExpense ? $lastExpense->total_expense : 'N/A';
                    }),
            ])
            ->filters([
                // Add filters if necessary
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TrainerVisitsRelationManager::class,
            // WalletPaymentLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountsExpenses::route('/'),
            'create' => Pages\CreateAccountsExpenses::route('/create'),
            'edit' => Pages\EditAccountsExpenses::route('/{record}/edit'),
            'view' => ViewAccountsExpenses::route('/{record}'),

        ];
    }
}
