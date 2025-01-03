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


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['accounts', 'accounts_head' ]);
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
                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->required()
                            ->rules(['min:1']) // Ensure minimum amount of 1
                            ->helperText('Enter the amount to top-up.'),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'credit_card' => 'Credit Card',
                            ])
                            ->required(),

                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->nullable()
                            ->helperText('Optional reference number for the payment.'),

                        DatePicker::make('payment_date')
                            ->required(),

                        FileUpload::make('payment_proof')
                            ->label('Payment Proof')
                            ->directory('payment_proofs')
                            ->nullable(),
                    ])
                    ->action(function (array $data, User $record) {
                        // Handle file upload for payment proof
                        $paymentProofPath = null;
                        if (isset($data['payment_proof'])) {
                            $paymentProofPath = $data['payment_proof']->store('payment_proofs', 'public');
                        }

                        // Process the top-up
                        $amount = $data['amount'];

                        // Update the user's wallet balance
                        $record->wallet_balance += $amount;

                        $record->total_amount_given += $amount;

                        $record->amount_to_close += $amount;

                        $record->save();

                        // Log the wallet top-up transaction
                        WalletLog::create([
                            'user_id' => $record->id,
                            'company_id' => $record->company_id,
                            'amount' => $amount,
                            'payment_date' => $data['payment_date'],
                            'type' => 'credit',
                            'description' => 'Wallet top-up by admin',
                            'payment_method' => $data['payment_method'],
                            'reference_number' => $data['reference_number'],
                            'payment_proof' => $paymentProofPath,
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
