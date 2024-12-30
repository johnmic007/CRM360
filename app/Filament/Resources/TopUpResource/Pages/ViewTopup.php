<?php

namespace App\Filament\Resources\TrainerVisitResource\Pages;

use App\Filament\Resources\TopUpResource;
use App\Models\User;
use App\Models\WalletLog;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;

class ViewTopUp extends ViewRecord
{
    protected static string $resource = TopUpResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('TopUp')
                ->label('Top-Up Wallet')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->visible(fn () => auth()->user()->hasRole('accounts_head'))
                ->modalHeading('Top-Up Wallet')
                ->form([
                    TextInput::make('amount')
                        ->label('Amount')
                        ->numeric()
                        ->required()
                        ->rules(['min:1'])
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
                        ->label('Payment Date')
                        ->required(),

                    FileUpload::make('payment_proof')
                        ->label('Payment Proof')
                        ->image()
                        ->directory('payment_proofs')
                        ->nullable(),
                ])
                ->action(function (array $data, User $record) {
                    // Handle file upload for payment proof
                    $paymentProofPath = null;
                    if (!empty($data['payment_proof'])) {
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
                ->requiresConfirmation(),
        ];
    }
}
