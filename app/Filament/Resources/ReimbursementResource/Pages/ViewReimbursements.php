<?php

namespace App\Filament\Resources\ReimbursementResource\Pages;

use App\Filament\Resources\ReimbursementResource;
use App\Models\WalletLog;
use App\Models\CompanyTransaction;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;

class ViewReimbursements extends ViewRecord
{
    protected static string $resource = ReimbursementResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('processReimbursement')
                ->label('Process Reimbursement')
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Process Reimbursement')
                ->form(function () {
                    $reimbursement = $this->record;
                    
                    return [
                        TextInput::make('amount')
                            ->label('Amount to Reimburse')
                            ->numeric()
                            ->required()
                            ->rules(['max:' . $reimbursement->amount_remaining])
                            ->helperText('Maximum: ' . $reimbursement->amount_remaining),

                        Select::make('selected_credit_logs')
                            ->label('Select Credit Transactions')
                            ->options(
                                CompanyTransaction::query()
                                    ->where('type', 'credit') // Only credit transactions
                                    ->where('balance', '>', 0) // Transactions with balance
                                    ->get()
                                    ->mapWithKeys(function ($transaction) {
                                        return [$transaction->id => "{$transaction->transaction_id} (Available: {$transaction->balance} INR)"];
                                    })
                            )
                            ->searchable()
                            ->required(),

                        DatePicker::make('payment_date')->required(),

                        FileUpload::make('payment_proof')
                            ->label('Payment Proof')
                            ->directory('reimbursement_proofs')
                            ->nullable(),
                    ];
                })
                ->action(function (array $data) {
                    $reimbursement = $this->record;
                    $user = $reimbursement->user;

                    // Ensure `selected_credit_logs` is treated as an array
                    $selectedCreditLogIds = is_array($data['selected_credit_logs'])
                        ? $data['selected_credit_logs']
                        : [$data['selected_credit_logs']];

                    $selectedTransactions = CompanyTransaction::whereIn('id', $selectedCreditLogIds)->get();
                    $remainingAmount = $data['amount'];
                    $includedCreditLogs = [];

                    foreach ($selectedTransactions as $transaction) {
                        if ($remainingAmount <= 0) {
                            break;
                        }

                        $deduction = min($transaction->balance, $remainingAmount);
                        $transaction->balance -= $deduction;
                        $transaction->save();
                        $remainingAmount -= $deduction;
                        $includedCreditLogs[] = $transaction->id;
                    }

                    // Update user's wallet balance
                    $user->wallet_balance += $data['amount'];
                    $user->save();

                    // Update reimbursement record
                    $reimbursement->amount_remaining -= $data['amount'];
                    if ($reimbursement->amount_remaining <= 0) {
                        $reimbursement->status = 'completed';
                    }
                    $reimbursement->save();

                    // Log the reimbursement in WalletLog
                    WalletLog::create([
                        'user_id' => $user->id,
                        'company_id' => $user->company_id,
                        'amount' => $data['amount'],
                        'balance' => $user->wallet_balance,
                        'type' => 'credit',
                        'credit_type' => 'reimbursement',

                        'wallet_logs' => json_encode($includedCreditLogs), // Store linked credit log IDs
                        'description' => 'Reimbursement processed and added to wallet balance.',
                        'payment_date' => $data['payment_date'],
                        'payment_proof' => $data['payment_proof'] ?? null,
                    ]);

                    Notification::make()
                        ->title('Reimbursement Processed')
                        ->body('The reimbursement has been processed and added to the wallet balance.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
