<?php

namespace App\Filament\Resources\AccountsClosingResource\Pages;

use App\Filament\Resources\AccountsClosingResource;
use App\Models\Reimbursement;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class ViewAccountsClosing extends ViewRecord
{
    protected static string $resource = AccountsClosingResource::class;

   
    protected function getActions(): array
    {
        return [
            Action::make('closeAccount')
                ->label('Close Account')
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    $user = User::find($this->record->user_id);

                    if (!$user) {
                        Notification::make()
                            ->title('User Not Found')
                            ->danger()
                            ->send();
                        return;
                    }

                    $walletBalance = $user->wallet_balance;

                    if ($walletBalance < 0) {
                        // Create a reimbursement for the negative balance
                        $reimbursement = Reimbursement::create([
                            'user_id' => $user->id,
                            'amount_due' => abs($walletBalance),
                            'amount_covered' => 0,
                            'amount_remaining' => abs($walletBalance),
                            'status' => 'pending',
                            'notes' => 'Negative wallet balance reimbursed on account closure.',
                        ]);

                        Notification::make()
                            ->title('Reimbursement Created')
                            ->body('Reimbursement for the negative balance has been recorded.')
                            ->warning()
                            ->send();
                    }

                    // Set wallet balance to 0 and save user
                    $user->wallet_balance = 0;
                    $user->save();

                    Notification::make()
                        ->title('Account Closed')
                        ->success()
                        ->body('The account has been closed successfully.')
                        ->send();
                }),
        ];
    }

 
}
