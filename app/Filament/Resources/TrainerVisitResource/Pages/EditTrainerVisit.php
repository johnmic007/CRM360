<?php

namespace App\Filament\Resources\TrainerVisitResource\Pages;

use App\Filament\Resources\TrainerVisitResource;
use App\Models\TrainerVisit;
use App\Models\User;
use App\Models\WalletLog;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditTrainerVisit extends EditRecord
{
    protected static string $resource = TrainerVisitResource::class;

    protected function getActions(): array
    {
        return [
            // 1. SALES VERIFY

            Action::make('verif')
                ->label('Sales Verified')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn() => Auth::user()->hasRole('sales_operation'))
                ->hidden(fn() => $this->record->verify_status !== 'verified'), // Only show when `verify_status` is 'verified'





            Action::make('answerClarification')
                ->label('Answer Clarification')
                ->color('info')
                ->icon('heroicon-o-pencil')
                ->visible(fn() => Auth::id() === $this->record->user_id && $this->record->verify_status === 'clarification')
                ->modalHeading(fn() => 'Clarification: ' . $this->record->clarification_question) // Display the question in the modal heading
                ->modalWidth('lg') // Set a wider modal for better visibility
                ->form([
                    Textarea::make('clarification_answer')
                        ->label('Your Answer')
                        ->placeholder('Provide your answer...')
                        ->required()
                        ->rows(6), // Adjust rows for better readability
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->record->clarification_answer = $data['clarification_answer'];
                    $this->record->verify_status = 'answered';
                    $this->record->save();

                    Notification::make()
                        ->title('Clarification Answered')
                        ->body('Your answer has been submitted ')
                        ->success()
                        ->send();
                }),


            Action::make('re')
                ->label('Requested For Clarification')
                ->color('warning')
                ->icon('heroicon-o-question-mark-circle')
                ->visible(fn() => Auth::user()->hasRole('sales_operation'))
                ->hidden(fn() => $this->record->verify_status !== 'clarification'), // Only show when `verify_status` is 'verified'


            Action::make('verifyBySales')
                ->label('Sales Verify')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn() => Auth::user()->hasRole('sales_operation'))
                ->hidden(fn() => in_array($this->record->verify_status, ['verified', 'rejected', 'clarification']))
                ->requiresConfirmation()
                ->action(function () {
                    // If already verified, do nothing
                    if ($this->record->verify_status === 'verified') {
                        Notification::make()
                            ->title('Already Verified')
                            ->warning()
                            ->send();
                        return;
                    }

                    // Mark as verified
                    $this->record->verify_status = 'verified';
                    $this->record->verified_by = Auth::id();
                    $this->record->verified_at = now();
                    $this->record->save();

                    Notification::make()
                        ->title('Verified Successfully')
                        ->success()
                        ->send();
                }),

            // 2. SALES REQUEST CLARIFICATION
            Action::make('requestClarification')
                ->label('Request Clarification')
                ->color('warning')
                ->icon('heroicon-o-question-mark-circle')
                ->visible(fn() => Auth::user()->hasRole('sales_operation'))
                ->hidden(fn() => in_array($this->record->verify_status, ['verified', 'rejected', 'clarification']))
                ->form([
                    Textarea::make('clarification_question')
                        ->label('Clarification Question')
                        ->placeholder('Enter the clarification question...')
                        ->required(),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    // Update the record with clarification status and question
                    $this->record->verify_status = 'clarification';
                    $this->record->clarification_question = $data['clarification_question']; // Assuming the column exists in the database
                    $this->record->verified_by = Auth::id();
                    $this->record->verified_at = now();
                    $this->record->save();

                    Notification::make()
                        ->title('Clarification Requested')
                        ->body('Your clarification question has been submitted.')
                        ->warning()
                        ->send();
                }),


            // 3. ACCOUNTS APPROVE
            Action::make('approveByAccounts')
                ->label('Accounts Approve')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->visible(fn() => Auth::user()->hasAnyRole(['accounts', 'accounts_head']))
                ->disabled(fn() => (
                    // Disable if not verified by Sales yet or already approved
                    $this->record->verify_status !== 'verified' ||
                    $this->record->approval_status === 'approved' ||
                    $this->record->approval_status === 'rejected'
                ))
                ->requiresConfirmation(fn() => $this->record->approval_status !== 'approved')
                ->action(function () {
                    // Must be verified by Sales first
                    if ($this->record->verify_status !== 'verified') {
                        Notification::make()
                            ->title('Not Verified')
                            ->danger()
                            ->body('Sales has not yet verified this visit.')
                            ->send();
                        return;
                    }

                    // If already approved, do nothing
                    if ($this->record->approval_status === 'approved') {
                        Notification::make()
                            ->title('Already Approved')
                            ->warning()
                            ->send();
                        return;
                    }

                    // Deduct from wallet
                    $record = $this->record;
                    $user = User::find($record->user_id);
                    $totalExpense = $record->total_expense;

                    if (!$user) {
                        Notification::make()
                            ->title('User Not Found')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Check wallet balance
                    if ($user->wallet_balance < $totalExpense) {
                        Notification::make()
                            ->title('Insufficient Balance')
                            ->danger()
                            ->body('The user does not have enough balance.')
                            ->send();
                        return;
                    }

                    // Deduct wallet
                    $user->wallet_balance -= $totalExpense;
                    $user->save();

                    // Log this deduction
                    WalletLog::create([
                        'user_id' => $user->id,
                        'amount' => $totalExpense,
                        'type' => 'debit',
                        'description' => 'Trainer visit expenses approved by Accounts',
                        'approved_by' => Auth::id(),
                    ]);

                    // Update approval fields
                    $record->approval_status = 'approved';
                    $record->approved_by = Auth::id();
                    $record->approved_at = now();
                    $record->save();

                    Notification::make()
                        ->title('Approval Successful')
                        ->body('This visit has been fully approved, and the wallet has been updated.')
                        ->success()
                        ->send();
                }),

            // 4. ACCOUNTS REJECT
            Action::make('rejectByAccounts')
                ->label('Accounts Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() => Auth::user()->hasAnyRole(['accounts', 'accounts_head']))
                ->disabled(fn() => (
                    // Disable if not verified yet or already final-approved/rejected
                    $this->record->verify_status !== 'verified' ||
                    $this->record->approval_status === 'approved' ||
                    $this->record->approval_status === 'rejected'
                ))
                ->requiresConfirmation()
                ->action(function () {
                    if ($this->record->verify_status !== 'verified') {
                        Notification::make()
                            ->title('Cannot Reject')
                            ->danger()
                            ->body('Sales has not verified this visit, so it can’t be rejected by Accounts yet.')
                            ->send();
                        return;
                    }

                    // Mark as rejected
                    $this->record->approval_status = 'rejected';
                    $this->record->approved_by = Auth::id();
                    $this->record->approved_at = now();
                    $this->record->save();

                    Notification::make()
                        ->title('Visit Rejected')
                        ->warning()
                        ->body('This visit has been rejected by Accounts.')
                        ->send();
                }),
        ];
    }
}
