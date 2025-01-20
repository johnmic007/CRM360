<?php

namespace App\Filament\Resources\AccountsClosingResource\Pages;

use App\Filament\Resources\AccountsClosingResource;
use App\Models\Reimbursement;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;


class EditAccountsClosing extends EditRecord
{
    protected static string $resource = AccountsClosingResource::class;

   
    protected function getFormActions(): array
    {
        return [];
    }


    protected function getActions(): array
    {
        return [
            Action::make('closeAccount')
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->label(fn () => $this->record->balance < 0 
                ? "Close Account (Reimbursement: {$this->record->balance})"
                : "Close Account")
            ->icon('heroicon-o-lock-closed')
            ->visible(fn () => !$this->record->is_closed) // Hide if is_closed is true
            ->color('danger')
            ->requiresConfirmation(fn () => $this->record->balance < 0 
            ? "This account has a negative balance of {$this->record->balance}. A reimbursement of " . abs($this->record->balance) . " will be created for this amount."
            : "Are you sure you want to close this account?")
                         ->action(function () {
                    $walletLog = $this->record;
    
                    if ($walletLog->is_closed) {
                        Notification::make()
                            ->title('Already Closed')
                            ->danger()
                            ->body('This account is already closed.')
                            ->send();
                        return;
                    }
    
                    if ($walletLog->balance < 0) {
                        // Create a reimbursement for the negative balance
                        Reimbursement::create([
                            'user_id' => $walletLog->user_id,
                            'amount_due' => abs($walletLog->balance),
                            'amount_covered' => 0,
                            'amount_remaining' => abs($walletLog->balance),
                            'status' => 'pending',
                            'notes' => 'Negative wallet log balance reimbursed on account closure.',
                        ]);
    
                        Notification::make()
                            ->title('Reimbursement Created')
                            ->body("Reimbursement for the negative balance of {$walletLog->balance} has been recorded.")
                            ->warning()
                            ->send();
                    }
    
                    // Mark the wallet log as closed
                    $walletLog->is_closed = true;
                    $walletLog->save();
    
                    Notification::make()
                        ->title('Account Closed')
                        ->success()
                        ->body('The account has been closed successfully.')
                        ->send();
                }),


                Action::make('alreadyClosed')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->label('This account has already been closed.')
                ->visible(fn () => $this->record->is_closed) // Show only if is_closed is true
                ->disabled(),
        ];
    }
    

}
