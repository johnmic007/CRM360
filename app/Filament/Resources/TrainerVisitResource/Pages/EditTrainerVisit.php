<?php

namespace App\Filament\Resources\TrainerVisitResource\Pages;

use App\Filament\Resources\TrainerVisitResource;
use App\Models\User;
use App\Models\WalletLog;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;

use Filament\Notifications\Notification;

class EditTrainerVisit extends EditRecord
{
    protected static string $resource = TrainerVisitResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('approve')
                ->label(fn () => $this->record->approved_by ? 'Approved' : 'Approve Visit')
                ->icon(fn () => $this->record->approved_by ? 'heroicon-o-check' : 'heroicon-o-check-circle')
                ->color(fn () => $this->record->approved_by ? 'success' : 'primary')
                ->disabled(fn () => (bool) $this->record->approved_by)
                ->visible(fn () => auth()->user()->hasAnyRole(['admin', 'sales']))
                ->requiresConfirmation(fn () => !$this->record->approved_by)
                ->action(function () {
                    // Check if already approved
                    if ($this->record->approved_by) {
                        Notification::make()
                            ->title('Already Approved')
                            ->body('This visit has already been approved.')
                            ->warning()
                            ->send();
                        return;
                    }

                    $record = $this->record;

                    // Ensure the user exists
                    $user = User::find($record->user_id);
                    if (!$user) {
                        Notification::make()
                            ->title('Error')
                            ->body('User not found.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Calculate the total expenses
                    $totalExpense = $record->total_expense ;

                    // Check wallet balance
                    if ($user->wallet_balance < $totalExpense) {
                        Notification::make()
                            ->title('Insufficient Balance')
                            ->body('The user does not have enough balance in their wallet.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Deduct expenses and approve
                    $user->wallet_balance -= $totalExpense;
                    $user->save();

                    WalletLog::create([
                        'user_id' => $record->user_id,
                        'amount' => $totalExpense,
                        'type' => 'debit',
                        'description' => 'Trainer visit expenses approved',
                        'approved_by' => auth()->id(),
                    ]);

                    $record->approved_by = auth()->id();
                    $record->approval_status = 'approved';
                    $record->save();

                    Notification::make()
                        ->title('Approval Successful')
                        ->body('The visit has been approved and wallet updated.')
                        ->success()
                        ->send();
                })
                ->modalHeading('Approve Visit')
                ->modalSubheading('This will deduct the expenses from the user\'s wallet.')
                ->modalButton('Confirm Approval'),
        ];
    }
}
