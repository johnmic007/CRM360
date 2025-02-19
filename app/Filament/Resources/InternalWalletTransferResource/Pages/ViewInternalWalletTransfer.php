<?php

namespace App\Filament\Resources\InternalWalletTransferResource\Pages;

use App\Filament\Resources\InternalWalletTransferResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Pages\Actions\Action;
use Filament\Notifications\Notification;
use App\Models\InternalWalletTransfer;
use App\Models\WalletLog;
use Illuminate\Support\Facades\Auth;

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
        $transfer = $this->record;

        // Update transfer status
        $transfer->update([
            'approval_status' => 'Approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Deduct from sender's wallet
        WalletLog::create([
            'user_id' => $transfer->from_user_id,
            'amount' => -$transfer->amount,
            'credit_type' => 'internal_wallet_transfer',
            'description' => 'Transferred to ' . $transfer->toUser->name,
        ]);

        // Credit to receiver's wallet
        WalletLog::create([
            'user_id' => $transfer->to_user_id,
            'amount' => $transfer->amount,
            'credit_type' => 'internal_wallet_transfer',
            'description' => 'Received from ' . $transfer->fromUser->name,
        ]);

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
