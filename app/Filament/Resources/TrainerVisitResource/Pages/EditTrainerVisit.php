<?php

namespace App\Filament\Resources\TrainerVisitResource\Pages;

use App\Filament\Resources\TrainerVisitResource;
use App\Models\Reimbursement;
use App\Models\TrainerVisit;
use App\Models\User;
use App\Models\WalletLog;
use Filament\Forms\Components\Select;
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
            // ...
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
                ->form([
                    // Show a multiselect of the user’s available credit logs
                    Select::make('selected_credit_logs')
                        ->label('Select Credit Logs to Apply')
                        ->multiple()
                        ->options(function () {
                            // Only fetch credit logs for the same user
                            return WalletLog::query()
                                ->where('user_id', $this->record->user_id)
                                ->where('type', 'credit')
                                ->pluck('amount', 'id');
                        })
                        ->searchable(),
                ])
                ->action(function (array $data) {
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
            
                    $record = $this->record;
                    $user = User::find($record->user_id);
                    if (!$user) {
                        Notification::make()
                            ->title('User Not Found')
                            ->danger()
                            ->send();
                        return;
                    }
            
                    $totalExpense = $record->total_expense;
            
                    // Sum of selected credit logs
                    $selectedCreditLogIds = $data['selected_credit_logs'] ?? [];
                    $sumOfSelectedCredits = WalletLog::whereIn('id', $selectedCreditLogIds)->sum('amount');
            
                    // If no credit logs were selected or sum is zero, just check overall wallet
                    // (You can decide how you want to handle this scenario)
                    if ($sumOfSelectedCredits <= 0) {
                        $sumOfSelectedCredits = 0;
                    }
            
                    /**
                     * 1) Deduct from user’s overall wallet_balance if the user
                     *    wants to allow coverage from the general balance as well.
                     *    OR you can interpret that the only coverage is from these credit logs.
                     *
                     *    For demonstration, let's assume the entire sum must come from these credit logs + user's wallet.
                     */
                    $walletBalanceBefore = $user->wallet_balance;
            
                    // Here, "how much of the expense can be covered" is the min of $sumOfSelectedCredits + $walletBalanceBefore
                    // and $totalExpense, if you want to let them combine “credit logs” + “wallet_balance”.
                    // If you do not want to combine, just use $sumOfSelectedCredits alone.
                    $maxCoverage = $sumOfSelectedCredits + $walletBalanceBefore;
            
                    if ($maxCoverage < $totalExpense) {
                        // The user can't fully cover the expense from selected credits + wallet
                        // => We'll cover what we can and create a reimbursement for the difference
                        $amountCovered = $maxCoverage;
                        $amountRemaining = $totalExpense - $amountCovered;
            
                        // Deduct from the selected credit logs first
                        // (One might implement more advanced logic to actually decrement each selected credit log’s “balance”,
                        //  if your credit logs are individually trackable. 
                        //  Or if each credit log is a simple record, just convert them to 'used' or something.)
                        // For simplicity, we won't do per-credit-log partial usage here, but you *could* add that logic.
            
                        // Next, reduce the user’s wallet_balance by any leftover coverage
                        // after using up the selected credit logs. E.g., if sumOfSelectedCredits < maxCoverage,
                        // we pull the rest from the user’s wallet_balance if available.
                        // This is just an example approach:
                        $creditsUsed = $sumOfSelectedCredits;
                        $fromWallet = $maxCoverage - $creditsUsed;
            
                        // Decrement the user’s wallet_balance by $fromWallet
                        $user->wallet_balance = $walletBalanceBefore - $fromWallet;
                        $user->save();
            
                        // Log wallet usage from the user’s wallet (a single debit entry).
                        if ($fromWallet > 0) {
                            WalletLog::create([
                                'user_id'          => $user->id,
                                'trainer_visit_id' => $record->id,
                                'amount'           => $fromWallet,
                                'type'             => 'debit',
                                'credit_type' => 'accounts aprroval',

                                'description'      => 'Trainer visit partial coverage from user’s wallet',
                                'approved_by'      => Auth::id(),
                            ]);
                        }
            
                        // If you want separate logs for each credit log used,
                        // you'd update each record individually. For now, just 1 combined “debit” for the total used.
            
                        // Create a new Reimbursement record for the difference
                        Reimbursement::create([
                            'trainer_visit_id' => $record->id,
                            'user_id'          => $user->id,
                            'amount_due'       => $totalExpense,
                            'amount_covered'   => $amountCovered,
                            'amount_remaining' => $amountRemaining,
                            'status'           => 'pending', // or however you want to track
                            'notes'            => 'Insufficient wallet. Pending additional reimbursement.',
                        ]);
            
                        // Mark the record as approved but partially covered
                        $record->approval_status = 'approved'; 
                        $record->approved_by = Auth::id();
                        $record->approved_at = now();
                        $record->save();
            
                        Notification::make()
                            ->title('Partially Approved')
                            ->body("User's wallet and selected credits do not cover full expense. A reimbursement record has been created.")
                            ->warning()
                            ->send();
                    } else {
                        // We can fully cover the expense from the selected credit logs + user wallet
                        // 1) Deduct the full expense from the sumOfSelectedCredits first
                        // 2) If needed, use some from the user’s wallet
            
                        $remainingExpense = $totalExpense;
                        $creditsUsed = min($sumOfSelectedCredits, $remainingExpense);
                        $remainingExpense -= $creditsUsed;
            
                        // If there's still expense left, take it from the user’s wallet
                        $fromWallet = 0;
                        if ($remainingExpense > 0) {
                            $fromWallet = $remainingExpense;
                            $user->wallet_balance = $walletBalanceBefore - $fromWallet;
                            $user->save();
                            $remainingExpense = 0;
                        }
            
                        // Log the usage from wallet
                        if ($fromWallet > 0) {
                            WalletLog::create([
                                'user_id'          => $user->id,
                                'trainer_visit_id' => $record->id,
                                'amount'           => $fromWallet,
                                'type'             => 'debit',
                                'credit_type' => 'accounts aprroval',

                                'description'      => 'Full coverage (partial from wallet)',
                                'approved_by'      => Auth::id(),
                            ]);
                        }
            
                        // If you want to break down usage by each credit log, do it here
                        // For simplicity, we assume these “credit logs” might be intangible or 
                        // do not need partial usage. If they do, you’d update them individually.
            
                        // Nothing left to reimburse => can fully approve
                        $record->approval_status = 'approved';
                        $record->approved_by = Auth::id();
                        $record->approved_at = now();
                        $record->save();
            
                        Notification::make()
                            ->title('Approval Successful')
                            ->body('This visit has been fully covered and approved. Wallet logs updated.')
                            ->success()
                            ->send();
                    }
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
