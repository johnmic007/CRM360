<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyAccountResource\Pages;
use App\Filament\Resources\CompanyAccountResource\Pages\ViewCompanyAccounts;
use App\Filament\Resources\CompanyAccountResource\RelationManagers\CompanyTransactionRelationManager;
use App\Models\CompanyTransaction;
use App\Models\WalletLog;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompanyAccountResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Top Ups'; // Singular form
    protected static ?string $pluralLabel = 'Topups';
    protected static ?string $navigationGroup = 'Finance Management';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'company' ,  'accounts_head']);
    }


    public static function form(Forms\Form $form): Forms\Form
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
                    ->label('User Name')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->getStateUsing(fn($record) => $record->roles->pluck('name')->join(', ')),

                TextColumn::make('wallet_balance')
                    ->label('Wallet Balance')
                    ->getStateUsing(fn($record) => $record->wallet_balance . ' INR'),
            ])
            ->filters([])
            ->actions([
                Action::make('topUp')
                    ->label('Top-Up Wallet')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->modalHeading('Top-Up Wallet')

                    ->form([
                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->required()
                            ->placeholder('Enter amount to top-up'),
                        TextInput::make('description')
                            ->label('Description')
                            ->placeholder('Enter a description for the transaction')
                            ->required(),

                        DatePicker::make('requested_at'),
                        DatePicker::make('issued_at'),

                    ])
                    ->action(function (array $data, User $record) {
                        // Ensure the company has enough balance
                        if ($record->roles->contains('accounts_head')) {
                            Notification::make()
                                ->title('Error')
                                ->body('Cannot top-up the Accounts Head wallet.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Top-up wallet balance
                        $record->wallet_balance += $data['amount'];
                        $record->save();

                        WalletLog::create([
                            'user_id' => $record->id,
                            'amount' => $data['amount'],
                            'balance' => $data['amount'],

                            'type' => 'credit',
                            'credit_type' => 'company credit',

                            'description' => $data['description'],
                            'approved_by' => auth()->id(),
                        ]);


                        $transaction = new \App\Models\CompanyTransaction([
                            'amount' => $data['amount'],
                            'balance' => $data['amount'],

                            'requested_at' => $data['requested_at'],
                            'issued_at' => $data['issued_at'],

                            'type' => 'credit',
                            'performed_by' => auth()->id(),
                            'wallet_user_id' => $record->id,
                            'description' => $data['description'],
                        ]);
                    
                        // Save the transaction to generate the ID
                        $transaction->save();
                    
                        // Generate the transaction ID using the instance method
                        $transactionId = $transaction->generateTransactionId();

                        $transaction->transaction_id = $transactionId;

                        $transaction->save();

                        // Notify the admin of success
                        Notification::make()
                            ->title('Wallet Top-Up Successful')
                            ->body("Wallet has been topped up with {$data['amount']} INR.")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()

                ])
                ->paginated([10, 25,]);

          
    }

    public static function getRelations(): array
    {
        return [
            CompanyTransactionRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanyAccounts::route('/'),
            'create' => Pages\CreateCompanyAccount::route('/create'),
            'view' => ViewCompanyAccounts::route('/{record}'),

            'edit' => Pages\EditCompanyAccount::route('/{record}/edit'),
        ];
    }
}
