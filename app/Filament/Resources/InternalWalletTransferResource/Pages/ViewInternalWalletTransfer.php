<?php

namespace App\Filament\Resources\InternalWalletTransferResource\Pages;

use App\Models\WalletLog;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\InternalWalletTransfer;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\InternalWalletTransferResource;

class ViewInternalWalletTransfer extends ViewRecord
{
    protected static string $resource = InternalWalletTransferResource::class;

    protected function getActions(): array
    {
        return [
            // Approve Transfer
            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn() => Auth::user()->hasRole('accounts_head') && $this->record->approval_status === 'Pending')
                ->requiresConfirmation()
                ->action(fn() => $this->approveTransfer()),

            // Reject Transfer
            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() => Auth::user()->hasRole('accounts_head') && $this->record->approval_status === 'Pending')
                ->requiresConfirmation()
                ->action(fn() => $this->rejectTransfer()),
        ];
    }

    // ✅ Function to Approve Transfer
    protected function approveTransfer()
{
    Log::info('🚀 Approve Transfer Function Called', ['transfer_id' => $this->record->id]);

    $transfer = $this->record;

    // Update transfer status
    $transfer->update([
        'approval_status' => 'Approved',
        'approved_by' => Auth::id(),
        'approved_at' => now(),
    ]);

    Log::info('✅ Transfer Approved:', ['transfer_id' => $transfer->id]);

    try {
        // Deduct from sender's wallet (debit)
        $debitLog = WalletLog::create([
            'user_id' => $transfer->from_user_id,
            'amount' => $transfer->amount,
            'type' => 'debit',
            'credit_type' => 'internal_wallet_transfer',
            'description' => 'Transferred to ' . optional($transfer->toUser)->name,
        ]);

        Log::info('💰 Debit Log Created', [
            'user_id' => $transfer->from_user_id,
            'amount' => $transfer->amount,
            'wallet_log_id' => $debitLog->id
        ]);

        // Credit to receiver's wallet (credit)
        $creditLog = WalletLog::create([
            'user_id' => $transfer->to_user_id,
            'amount' => $transfer->amount,
            'type' => 'credit',
            'credit_type' => 'internal_wallet_transfer',
            'description' => 'Received from ' . optional($transfer->fromUser)->name,
        ]);

        Log::info('💰 Credit Log Created', [
            'user_id' => $transfer->to_user_id,
            'amount' => $transfer->amount,
            'wallet_log_id' => $creditLog->id
        ]);

        // Dump and Die to check if logs are created
        dd($debitLog, $creditLog);

    } catch (\Exception $e) {
        Log::error('❌ Wallet Log Creation Failed', ['error' => $e->getMessage()]);
        dd('❌ Wallet Log Creation Failed:', $e->getMessage());
    }

    Log::info('📌 Wallet Log Entries Created', ['transfer_id' => $transfer->id]);

    // Notify User
    Notification::make()
        ->title('Transfer Approved')
        ->body('The wallet transfer has been successfully approved.')
        ->success()
        ->send();
}

    // ✅ Function to Reject Transfer
    protected function rejectTransfer()
    {
        $transfer = $this->record;

        // Update status
        $transfer->update([
            'approval_status' => 'Rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Notify User
        Notification::make()
            ->title('Transfer Rejected')
            ->body('The wallet transfer request has been rejected.')
            ->danger()
            ->send();
    }
}
