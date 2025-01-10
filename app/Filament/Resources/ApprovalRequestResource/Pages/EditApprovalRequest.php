<?php

namespace App\Filament\Resources\ApprovalRequestResource\Pages;

use App\Filament\Resources\ApprovalRequestResource;
use App\Models\SalesLeadManagement;
use App\Models\SchoolUser;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditApprovalRequest extends EditRecord
{
    protected static string $resource = ApprovalRequestResource::class;

    protected function getFormActions(): array
    {
        return []; // Remove Save Changes and Cancel buttons
    }

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
                    // Update the status to Approved
                    $this->record->update(['status' => 'Approved']);



                    // Create a new SalesLeadManagement record with correct data
                    // SalesLeadManagement::create([
                    //     'school_id' => $this->record->school_id,
                    //     'allocated_to' => $this->record->user_id, // Assign `user_id` to `allocated_to`
                    //     'company_id' => $this->record->company_id,
                    //     'status' => 'School Nurturing',
                    // ]);

                    SchoolUser::create([
                        'school_id' => $this->record->school_id,
                        'user_id' => $this->record->user_id, // Assign `user_id` to `allocated_to`

                    ]);

                    $schoolName = $this->record->school->name ?? 'Unknown School';


                    Notification::make()
                        ->title('School Allocated')
                        ->body("You have been allocated to the school '$schoolName'.")
                        ->success()
                        ->sendToDatabase($this->record->user);

                    // Notify the user
                    Notification::make()
                        ->title('Request Approved')
                        ->success()
                        ->send();

                    // Redirect back to the resource index
                    $this->redirect($this->getResource()::getUrl('index'));
                })
                ->visible(fn() => $this->record->status === 'Pending'),

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
                ->visible(fn() => $this->record->status === 'Pending' && $user->id === $this->record->manager_id),

            // Approved button (Visible to all roles if already approved)
            Action::make('Approved')
                ->label('Approved')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->disabled()
                ->visible(fn() => $this->record->status === 'Approved'),

            // Rejected button (Visible to all roles if already rejected)
            Action::make('Rejected')
                ->label('Rejected')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->disabled()
                ->visible(fn() => $this->record->status === 'Rejected'),

            // Pending status (Visible to all roles if still pending)
            Action::make('Pending')
                ->label('Pending')
                ->color('secondary')
                ->icon('heroicon-o-clock')
                ->disabled()
                ->visible(fn() => $this->record->status === 'Pending'),
        ];
    }
}
