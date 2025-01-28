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
                ->form([
                    TextInput::make('amount')
                        ->label('Amount to Reimburse')
                        ->numeric()
                        ->required()
                        ->rules(['max:' . $this->record->amount_remaining])
                        ->helperText('Maximum: ' . $this->record->amount_remaining),
    
                    DatePicker::make('payment_date')->required(),
    
                    FileUpload::make('payment_proof')
                        ->label('Payment Proof')
            ->optimize('webp')                ->disk('s3')
                        ->directory('CRM')
                        ->directory('reimbursement_proofs')
                        ->nullable(),
                ])
                ->action(function (array $data) {
                    $reimbursement = $this->record;
                    $user = $reimbursement->user;
    
                    $amountToReimburse = $data['amount'];
    
                    // Update user's wallet balance
                    $user->wallet_balance += $amountToReimburse;
                    $user->save();
    
                    // Update reimbursement record
                    $reimbursement->amount_remaining -= $amountToReimburse;
                    if ($reimbursement->amount_remaining <= 0) {
                        $reimbursement->status = 'completed';
                    }
                    $reimbursement->save();
    
                    // Log the reimbursement in WalletLog
                    WalletLog::create([
                        'user_id' => $user->id,
                        'company_id' => $user->company_id,
                        'amount' => $amountToReimburse,
                        'balance' => $user->wallet_balance,
                        'type' => 'credit',
                        'credit_type' => 'reimbursement',
                        'description' => 'Reimbursement processed and added to wallet balance.',
                        'payment_date' => $data['payment_date'],
                        'payment_proof' => $data['payment_proof'] ?? null,
                    ]);
    
                    Notification::make()
                        ->title('Reimbursement Processed')
                        ->body('The reimbursement has been processed and added to the wallet balance.')
                        ->success()
                        ->send();
                })
                ->visible(fn () => auth()->user()->hasRole('accounts_head') && $this->record->status !== 'completed'), // Restrict visibility to accounts_head role and incomplete status
            ];
    }
    
}
