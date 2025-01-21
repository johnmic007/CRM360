<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TopUpResource\Pages;
use App\Filament\Resources\TopUpResource\RelationManagers;
use App\Filament\Resources\TrainerVisitResource\Pages\ViewTopUp;
use App\Filament\Resources\WalletLogsResource\RelationManagers\WalletLogsRelationManager;
use App\Models\TopUp;
use App\Models\User;
use App\Models\WalletLog;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TopUpResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $label = 'Topups'; // Singular form
    protected static ?string $pluralLabel = 'Topups';
    protected static ?string $navigationGroup = 'Finance Management';



    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['accounts', 'accounts_head']);
    }

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
                    ->label('User Name')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('wallet_balance')
                    ->searchable(),

                TextColumn::make('total_amount_given')
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->getStateUsing(fn($record) => $record->roles->pluck('name')->join(', ')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('TopUp')
                    ->label('Top-Up Wallet')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->visible(fn(User $record) => auth()->user()->hasRole('accounts_head'))
                    ->modalHeading('Top-Up Wallet')
                    ->form([
                        Select::make('transaction_id')
                            ->label('Select Transaction')
                            ->options(
                                \App\Models\CompanyTransaction::query()
                                    ->where('type', 'credit') // Only credit transactions
                                    ->where('balance', '>', 0) // Transactions with balance
                                    ->get()
                                    ->mapWithKeys(function ($transaction) {
                                        return [$transaction->id => "{$transaction->transaction_id} (Available: {$transaction->balance} INR)"];
                                    })
                            )
                            ->reactive()
                            ->required()
                            ->helperText(function (callable $get) {
                                $transactionId = $get('transaction_id');
                                if ($transactionId) {
                                    $transaction = \App\Models\CompanyTransaction::find($transactionId);
                                    return $transaction ? "Available balance: {$transaction->balance} INR" : 'No transaction selected.';
                                }
                                return 'Select a transaction to see the available balance.';
                            }),

                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->required()
                            ->rules(['min:1']) // Ensure minimum amount of 1
                            ->helperText('Enter the amount to top-up. Must not exceed the transaction balance.'),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                            ])
                            ->required(),

                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->nullable()
                            ->helperText('Optional reference number for the payment.'),


                        FileUpload::make('payment_proof')
                            ->label('Payment Proof')
                            ->directory('payment_proofs')  // Store the image in a specific directory
                            ->nullable(),

                        DatePicker::make('payment_date')
                            ->required(),
                    ])
                    ->action(function (array $data, User $record) {
                        // Fetch the selected transaction
                        $transaction = \App\Models\CompanyTransaction::find($data['transaction_id']);

                        if (!$transaction) {
                            throw new \Exception('Invalid transaction selected.');
                        }

                        // Validate the amount
                        if ($data['amount'] > $transaction->balance) {
                            throw new \Exception('The entered amount exceeds the transaction balance.');
                        }

                        // Process the top-up
                        $amount = $data['amount'];

                        // Deduct from the CompanyTransaction balance
                        $transaction->balance -= $amount;
                        $transaction->save();

                        // Update the user's wallet balance
                        $record->wallet_balance += $amount;
                        $record->total_amount_given += $amount;
                        $record->amount_to_close += $amount;
                        $record->save();


                        $lastLog = \App\Models\WalletLog::where('transaction_id', 'like', "{$transaction->transaction_id}-%")
                            ->orderBy('transaction_id', 'desc')
                            ->first();

                        $nextAlphabet = 'A';


                        if ($lastLog) {
                            $lastAlphabet = strtoupper(substr($lastLog->transaction_id, -1));
                            $nextAlphabet = chr(ord($lastAlphabet) + 1); // Increment the alphabet
                        }

                        $newTransactionId = "{$transaction->transaction_id}-{$nextAlphabet}";


                        // Log the wallet top-up transaction
                        $walletLog = WalletLog::create([
                            'user_id' => $record->id,

                            'company_id' => $record->company_id,
                            'amount' => $amount,
                            'balance' => $amount,
                            'credit_type' => 'accounts topup',
                            'transaction_id' => $newTransactionId,
                            'payment_date' => $data['payment_date'],
                            'payment_method' => $data['payment_method'],
                            'payment_proof' => $data['payment_proof'],
                            'type' => 'credit',
                            'description' => 'Wallet top-up from Company Transaction',
                            'payment_method' => 'CompanyTransaction',
                            'reference_number' => $data['reference_number'],
                            'payment_proof' => null, // No proof needed in this case
                        ]);

                        // Save the relationship in the pivot table
                        $transaction->walletLogs()->attach($walletLog->id, [
                            'type' => 'Top-Up', // Specify the type as "Top-Up"
                        ]);

                        // Send a database notification to the user
                        \Filament\Notifications\Notification::make()
                            ->title('Wallet Top-Up Successful')
                            ->body("Your wallet has been credited with an amount of $amount.")
                            ->success()
                            ->sendToDatabase($record);
                    })
                    ->requiresConfirmation()

            ]);
    }

    public static function getRelations(): array
    {
        return [
            WalletLogsRelationManager::class,



        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTopUps::route('/'),
            'create' => Pages\CreateTopUp::route('/create'),
            'edit' => Pages\EditTopUp::route('/{record}/edit'),
            'view' => ViewTopUp::route('/{record}'),

        ];
    }
}
