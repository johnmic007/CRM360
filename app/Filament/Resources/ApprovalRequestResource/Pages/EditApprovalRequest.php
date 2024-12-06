<?php

namespace App\Filament\Resources\ApprovalRequestResource\Pages;

use App\Filament\Resources\ApprovalRequestResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditApprovalRequest extends EditRecord
{
    protected static string $resource = ApprovalRequestResource::class;

    protected function getActions(): array
    {
        $user = Auth::user();

        return [
            // Approve button (Visible only to the manager)
            Action::make('Set to Approved')
                ->label('Approve')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->modalHeading('Approve Request')
                ->modalSubheading('Are you sure you want to approve this request?')
                ->action(function () {
                    $this->record->update(['status' => 'Approved']);
                    Notification::make()
                        ->title('Request Approved')
                        ->success()
                        ->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->visible(fn () => $this->record->status === 'Pending' && $user->id === $this->record->manager_id),

            // Reject button (Visible only to the manager)
            Action::make('Set to Rejected')
                ->label('Reject')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->modalHeading('Reject Request')
                ->modalSubheading('Are you sure you want to reject this request?')
                ->action(function () {
                    $this->record->update(['status' => 'Rejected']);
                    Notification::make()
                        ->title('Request Rejected')
                        ->danger()
                        ->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->visible(fn () => $this->record->status === 'Pending' && $user->id === $this->record->manager_id),

            // Approved button (Visible to all roles if already approved)
            Action::make('Approved')
                ->label('Approved')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->disabled()
                ->visible(fn () => $this->record->status === 'Approved'),

            // Rejected button (Visible to all roles if already rejected)
            Action::make('Rejected')
                ->label('Rejected')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->disabled()
                ->visible(fn () => $this->record->status === 'Rejected'),

            // Pending status (Visible to all roles if still pending)
            Action::make('Pending')
                ->label('Pending')
                ->color('secondary')
                ->icon('heroicon-o-clock')
                ->disabled()
                ->visible(fn () => $this->record->status === 'Pending'),
        ];
    }
}
