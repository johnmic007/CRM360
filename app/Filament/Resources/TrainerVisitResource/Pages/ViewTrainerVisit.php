<?php

namespace App\Filament\Resources\TrainerVisitResource\Pages;

use App\Filament\Resources\TrainerVisitResource;
use App\Models\Reimbursement;
use Filament\Resources\Pages\ViewRecord;

use App\Models\TrainerVisit;
use App\Models\User;
use App\Models\WalletLog;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\View;
use Filament\Notifications\Notification;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ViewTrainerVisit extends ViewRecord
{
    protected static string $resource = TrainerVisitResource::class;




    protected function getActions(): array
    {

        $prevRecordId = TrainerVisit::where('id', '<', $this->record->id)
            ->orderBy('id', 'desc')
            ->first()?->id;

        $nextRecordId = TrainerVisit::where('id', '>', $this->record->id)
            ->orderBy('id', 'asc')
            ->first()?->id;

        return [


            Action::make('previous')
                ->label('Previous')
                ->icon('heroicon-o-chevron-left')
                ->color('gray')
                ->tooltip('Go to the previous record') // Add a tooltip
                ->extraAttributes([
                    'class' => 'rounded-full px-4 py-2 shadow-md hover:bg-gray-100', // Styling for the button
                ])
                ->iconPosition('before') // Ensure the icon is before the label
                ->url(fn() => $prevRecordId ? route('filament.admin.resources.trainer-visits.view', $prevRecordId) : null)
                ->disabled(fn() => !$prevRecordId),

            Action::make('next')
                ->label('Next')
                ->icon('heroicon-o-chevron-right')
                ->color('gray')
                ->tooltip('Go to the next record') // Add a tooltip
                ->extraAttributes([
                    'class' => 'rounded-full px-4 py-2 shadow-md hover:bg-gray-100', // Styling for the button
                ])
                ->iconPosition('after') // Ensure the icon is after the label
                ->url(fn() => $nextRecordId ? route('filament.admin.resources.trainer-visits.view', $nextRecordId) : null)
                ->disabled(fn() => !$nextRecordId),


            Action::make('verif')
                ->label('Sales Verified')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn() => Auth::user()->hasRole('sales_operation'))
                ->hidden(fn() => $this->record->verify_status !== 'verified'), // Only show when `verify_status` is 'verified'


            Action::make('approved')

                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->hidden(fn() => $this->record->approval_status !== 'approved'), // Only show when `verify_status` is 'verified'









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
                ->form(function () {
                    $user = User::find($this->record->user_id);
                    $userId = $this->record->user_id;

                    // dd($userId);

                    $totalExpense = $this->record->total_expense;

                    // Fetch selected wallet logs for the user
                    $walletLogs = WalletLog::query()
                        ->where('user_id', $this->record->user_id)
                        ->where('type', 'credit')
                        ->where(function ($query) {
                            $query->where('is_closed', false)
                                ->orWhereNull('is_closed');
                        })
                        // ->where('balance', '>', 0) // Only fetch logs with a positive balance
                        ->get();

                    return [
                        TextInput::make('wallet_balance')
                            ->label('Wallet Balance')
                            ->default($user ? number_format($user->wallet_balance, 2) : '0.00')
                            ->disabled(), // Make the input read-only

                        // Select::make('selected_credit_logs')
                        // ->label('Select Credit Logs to Apply')
                        // ->options(function () use ($walletLogs) {
                        //     return $walletLogs->mapWithKeys(function ($log) {
                        //         return [
                        //             $log->id => "{$log->transaction_id} | Amount: {$log->amount} | Balance: {$log->balance}",
                        //         ];
                        //     });
                        // })

                        // Total Credit
                        TextInput::make('total_credit')
                            ->label('Total Credit')
                            ->disabled() // Make it read-only
                            ->reactive()
                            ->default(function () use ($userId) {
                                
                                return \App\Models\WalletLog::where('user_id', $userId)
                                    ->where('type', 'credit')
                                    ->whereNull('transaction_id')
                                    ->sum('amount'); 
                            }),

                        // Total Debit
                        TextInput::make('total_debit')
                            ->label('Total Debit')
                            ->disabled() // Make it read-only
                            ->reactive()
                            ->default(function () use ($userId) {
                                return \App\Models\WalletLog::where('user_id', $userId)
                                    ->where('type', 'debit')
                                    ->whereNull('transaction_id')
                                    ->sum('amount'); // Sum up all debit transactions
                            }),


                        Select::make('selected_credit_logs')
                            ->label('Select Credit Logs to Apply')
                            ->visible(function () use ($walletLogs, $userId) {
                                // Fetch credit transactions where transaction_id is null
                                $creditLogs = \App\Models\WalletLog::where('user_id', $userId)
                                ->where('type', 'credit')
                                ->whereNull('transaction_id')
                                    ->get();

                                // Fetch all debit transactions for the user
                                $debitLogs = \App\Models\WalletLog::where('user_id', $userId)
                                    ->where('type', 'debit')
                                    ->whereNull('transaction_id')
                                    ->get();

                                // Calculate total amounts
                                $totalCredits = $creditLogs->sum('amount');
                                $totalDebits = $debitLogs->sum('amount');

                                // dd(  $creditLogs ,  $totalCredits , $totalDebits  );

                                // Show the field only if the debit difference is greater
                                return $totalDebits > $totalCredits;
                            })
                            ->options(function () use ($walletLogs, $userId) {
                                // Fetch credit transactions where transaction_id is null
                                $creditLogs = \App\Models\WalletLog::where('user_id', $userId)
                                    ->where('type', 'credit')
                                    ->whereNotNull('transaction_id')
                                    ->get();

                                    // dd($creditLogs);

                                // Map the logs into options
                                return $creditLogs->mapWithKeys(function ($log) {
                                    return [
                                        $log->id => "{$log->transaction_id} | Amount: {$log->amount} | Balance: {$log->balance}",
                                    ];
                                });
                            })
                            ->searchable()
                            ->required()
                            ->reactive() // Make the field reactive to trigger updates
                            ->afterStateUpdated(function (callable $set, $state) use ($walletLogs, $totalExpense) {
                                // Filter the selected logs
                                $selectedLogs = $walletLogs->whereIn('id', $state);
                                $totalSelectedCredits = $selectedLogs->sum('balance');

                                // Calculate remaining expense
                                $remainingExpense = max(0, $totalExpense - $totalSelectedCredits);

                                // Update the reimbursement field dynamically
                                $set('remaining_due', number_format($remainingExpense, 2));
                            }),

                        // Remaining due preview
                        TextInput::make('remaining_due')
                            ->label('Remaining Due (Preview)')
                            ->disabled() // Show the calculated remaining due
                            ->visible(fn($get) => $get('remaining_due') > 0), // Only display if remaining expense is greater than 0


                        Textarea::make('remarks')
                            ->placeholder('Enter any remarks for this approval...')
                            ->label('Remarks'),
                    ];
                })
                ->action(function (array $data) {
                    Log::info('Starting approval action.', ['record_id' => $this->record->id]);

                    // Check if record is verified
                    if ($this->record->verify_status !== 'verified') {
                        Log::warning('Attempt to approve without verification.', ['record_id' => $this->record->id]);
                        Notification::make()
                            ->title('Not Verified')
                            ->danger()
                            ->body('Sales has not yet verified this visit.')
                            ->send();
                        return;
                    }

                    // Check if already approved
                    if ($this->record->approval_status === 'approved') {
                        Log::info('Record already approved.', ['record_id' => $this->record->id]);
                        Notification::make()
                            ->title('Already Approved')
                            ->warning()
                            ->send();
                        return;
                    }

                    $record = $this->record;
                    $user = User::find($record->user_id);

                    if (!$user) {
                        Log::error('User not found.', ['user_id' => $record->user_id]);
                        Notification::make()
                            ->title('User Not Found')
                            ->danger()
                            ->send();
                        return;
                    }

                    $totalExpense = $record->total_expense;
                    Log::info('Total expense calculated.', ['total_expense' => $totalExpense]);

                    // Process credit logs
                    $selectedCreditLogIds = $data['selected_credit_logs'] ?? [];
                    if (!is_array($selectedCreditLogIds)) {
                        $selectedCreditLogIds = [$selectedCreditLogIds];
                    }

                    $selectedLogs = WalletLog::whereIn('id', $selectedCreditLogIds)->get();
                    $remainingExpense = $totalExpense;

                    // Deduct from selected credit logs
                    foreach ($selectedLogs as $log) {
                        if ($remainingExpense <= 0) {
                            break;
                        }

                        $deduction = $remainingExpense; // Deduct the full remaining expense
                        $log->balance -= $deduction;   // Allow the balance to go negative

                        $log->save();

                        $remainingExpense -= $deduction;

                        Log::info('Deducted from wallet log.', [
                            'log_id' => $log->id,
                            'deduction' => $deduction,
                            'remaining_balance' => $log->balance,
                            'remaining_expense' => $remainingExpense,
                        ]);
                    }

                    // Deduct the remaining expense from the user's wallet balance (can go negative)
                    $user->wallet_balance -= $totalExpense;
                    $user->save();

                    $includedCreditLogs = $log->id ?? null;


                    Log::info('Wallet balance updated (can be negative).', [
                        'user_id' => $user->id,
                        'deducted_amount' => $totalExpense,
                        'new_wallet_balance' => $user->wallet_balance,
                    ]);

                    // Log full expense in WalletLog
                    WalletLog::create([
                        'user_id' => $user->id,
                        'trainer_visit_id' => $record->id,
                        'amount' => $totalExpense,
                        'type' => 'debit',
                        'wallet_logs' => $includedCreditLogs,
                        'credit_type' => 'accounts approval',
                        'description' => 'Full expense deducted, including selected credit logs and remaining balance.',
                        'approved_by' => Auth::id(),
                    ]);

                    Log::info('Wallet log created for full expense.', [
                        'user_id' => $user->id,
                        'amount' => $totalExpense,
                    ]);

                    // Approve the record
                    $record->approval_status = 'approved';
                    $record->approved_by = Auth::id();
                    $record->approved_at = now();
                    $record->save();

                    $record->approval_status = 'approved';
                    $record->approved_by = Auth::id();
                    $record->approved_at = now();
                    $record->remarks = $data['remarks'] ?? null; // Save remarks to the TrainerVisit record
                    $record->save();


                    Log::info('Record approved successfully.', ['record_id' => $record->id]);

                    Notification::make()
                        ->title('Approval Successful')
                        ->body('The visit has been approved. Wallet log details have been updated.')
                        ->success()
                        ->send();
                }),



            // ...
            // Action::make('approveByAccounts')
            //     ->label('Accounts Approve')
            //     ->icon('heroicon-o-check')
            //     ->color('primary')
            //     ->visible(fn() => Auth::user()->hasAnyRole(['accounts', 'accounts_head']))
            //     ->disabled(fn() => (
            //         // Disable if not verified by Sales yet or already approved
            //         $this->record->verify_status !== 'verified' ||
            //         $this->record->approval_status === 'approved' ||
            //         $this->record->approval_status === 'rejected'
            //     ))
            //     ->requiresConfirmation(fn() => $this->record->approval_status !== 'approved')
            //     ->form(function () {
            //         $user = User::find($this->record->user_id);
            //         $totalExpense = $this->record->total_expense;

            //         // Fetch selected wallet logs for the user
            //         $walletLogs = WalletLog::query()
            //             ->where('user_id', $this->record->user_id)
            //             ->where('type', 'credit')
            //             ->where('balance', '>', 0) // Only fetch logs with a positive balance
            //             ->get();

            //         return [
            //             TextInput::make('wallet_balance')
            //                 ->label('Wallet Balance')
            //                 ->default($user ? number_format($user->wallet_balance, 2) : '0.00')
            //                 ->disabled(), // Make the input read-only

            //             Select::make('selected_credit_logs')
            //                 ->label('Select Credit Logs to Apply')
            //                 ->options(function () use ($walletLogs) {
            //                     return $walletLogs->mapWithKeys(function ($log) {
            //                         return [
            //                             $log->id => "Amount: {$log->amount} | Balance: {$log->balance}",
            //                         ];
            //                     });
            //                 })
            //                 ->searchable()
            //                 ->required()
            //                 ->reactive() // Make the field reactive to trigger updates
            //                 ->afterStateUpdated(function (callable $set, $state) use ($walletLogs, $totalExpense) {
            //                     // Filter the selected logs
            //                     $selectedLogs = $walletLogs->whereIn('id', $state);
            //                     $totalSelectedCredits = $selectedLogs->sum('balance');

            //                     // Calculate remaining expense
            //                     $remainingExpense = max(0, $totalExpense - $totalSelectedCredits);

            //                     // Update the reimbursement field dynamically
            //                     $set('reimbursement_due', number_format($remainingExpense, 2));
            //                 }),

            //             // Reimbursement preview
            //             TextInput::make('reimbursement_due')
            //                 ->label('Reimbursement Amount (Preview)')
            //                 ->disabled() // Show the calculated reimbursement
            //                 ->visible(fn($get) => $get('reimbursement_due') > 0), // Only display if remaining expense is greater than 0
            //         ];
            //     })

            //     ->action(function (array $data) {

            //         Log::info('Starting approval action.', ['record_id' => $this->record->id]);

            //         // Check if record is verified
            //         if ($this->record->verify_status !== 'verified') {
            //             Log::warning('Attempt to approve without verification.', ['record_id' => $this->record->id]);
            //             Notification::make()
            //                 ->title('Not Verified')
            //                 ->danger()
            //                 ->body('Sales has not yet verified this visit.')
            //                 ->send();
            //             return;
            //         }

            //         // Check if already approved
            //         if ($this->record->approval_status === 'approved') {
            //             Log::info('Record already approved.', ['record_id' => $this->record->id]);
            //             Notification::make()
            //                 ->title('Already Approved')
            //                 ->warning()
            //                 ->send();
            //             return;
            //         }

            //         $record = $this->record;
            //         $user = User::find($record->user_id);

            //         if (!$user) {
            //             Log::error('User not found.', ['user_id' => $record->user_id]);
            //             Notification::make()
            //                 ->title('User Not Found')
            //                 ->danger()
            //                 ->send();
            //             return;
            //         }

            //         $totalExpense = $record->total_expense;
            //         Log::info('Total expense calculated.', ['total_expense' => $totalExpense]);

            //         // Process credit logs
            //         $selectedCreditLogIds = $data['selected_credit_logs'] ?? [];
            //         if (!is_array($selectedCreditLogIds)) {
            //             $selectedCreditLogIds = [$selectedCreditLogIds];
            //         }

            //         $selectedLogs = WalletLog::whereIn('id', $selectedCreditLogIds)->get();
            //         $remainingExpense = $totalExpense;
            //         $includedCreditLogs = null;

            //         // Deduct from selected credit logs
            //         foreach ($selectedLogs as $log) {
            //             if ($remainingExpense <= 0) {
            //                 break;
            //             }

            //             // Calculate reimbursement amount
            //         $reimbursementAmount = $selectedLogs->sum('balance') - $totalExpense;
            //         // dd($reimbursementAmount , $selectedLogs->sum('balance') , $log->balance );
            //         $reimbursementId = null;
            //         $finalReimbursementAmount = null;

            //         // Create reimbursement if necessary
            //         if ($reimbursementAmount < 0) {
            //             $reimbursement = Reimbursement::create([
            //                 'trainer_visit_id' => $record->id,
            //                 'user_id' => $user->id,
            //                 'amount_due' => $totalExpense,
            //                 'amount_covered' => $totalExpense - abs($reimbursementAmount),
            //                 'amount_remaining' => abs($reimbursementAmount),
            //                 'status' => 'pending',
            //                 'notes' => 'Reimbursement created due to insufficient wallet balance.',
            //             ]);

            //             $reimbursementId = $reimbursement->id;
            //             $finalReimbursementAmount = abs($reimbursementAmount);
            //             Log::info('Reimbursement record created.', ['reimbursement_id' => $reimbursementId]);
            //         }

            //             $deduction = min($log->balance, $remainingExpense);
            //             $log->balance -= $deduction;
            //             $log->save();

            //             $remainingExpense -= $deduction;
            //             $includedCreditLogs = $log->id;

            //             Log::info('Deducted from wallet log.', [
            //                 'log_id' => $log->id,
            //                 'deduction' => $deduction,
            //                 'remaining_balance' => $log->balance,
            //                 'remaining_expense' => $remainingExpense,
            //             ]);
            //         }



            //         // Deduct the full expense from the user's wallet balance
            //         $user->wallet_balance -= $totalExpense;
            //         $user->save();

            //         Log::info('Wallet balance updated (can be negative).', [
            //             'user_id' => $user->id,
            //             'deducted_amount' => $totalExpense,
            //             'new_wallet_balance' => $user->wallet_balance,
            //         ]);

            //         // Log full expense in WalletLog
            //         WalletLog::create([
            //             'user_id' => $user->id,
            //             'trainer_visit_id' => $record->id,
            //             'amount' => $totalExpense,
            //             'type' => 'debit',
            //             'credit_type' => 'accounts aprroval',
            //             'description' => 'Full expense deducted, including selected credit logs and remaining balance.',
            //             'approved_by' => Auth::id(),
            //             'wallet_logs' => $includedCreditLogs, // Store the linked credit log IDs
            //             'reimbursement_id' => $reimbursementId, // Link reimbursement if applicable
            //             'reimbursement_amount' => $finalReimbursementAmount, // Store reimbursement amount for reference
            //         ]);

            //         Log::info('Wallet log created for full expense.', [
            //             'user_id' => $user->id,
            //             'amount' => $totalExpense,
            //             'linked_credit_logs' => $includedCreditLogs,
            //             'reimbursement_id' => $reimbursementId,
            //         ]);

            //         // Approve the record
            //         $record->approval_status = 'approved';
            //         $record->approved_by = Auth::id();
            //         $record->approved_at = now();
            //         $record->save();
            //         Log::info('Record approved successfully.', ['record_id' => $record->id]);

            //         Notification::make()
            //             ->title('Approval Successful')
            //             ->body('The visit has been approved. Wallet log and reimbursement details have been updated.')
            //             ->success()
            //             ->send();
            //     }),

            // 4. ACCOUNTS REJECT
            Action::make('rejectByAccounts')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() => Auth::user()->hasAnyRole(['accounts', 'accounts_head']))
                ->hidden(fn() => $this->record->approval_status === 'approved')   // Only show when `verify_status` is 'verified'

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
