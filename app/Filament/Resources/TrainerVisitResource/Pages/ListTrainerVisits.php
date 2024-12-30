<?php

namespace App\Filament\Resources\TrainerVisitResource\Pages;

use App\Filament\Resources\TrainerVisitResource;
use App\Models\TrainerVisit;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;


use Filament\Resources\Components\Tab;


class ListTrainerVisits extends ListRecords
{
    protected static string $resource = TrainerVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Expense Log'), // Default creation action

            Actions\Action::make('extra_expenses')
                ->label('Add Extra Expenses')
                ->icon('heroicon-o-banknotes')
                ->modalHeading('Add Extra Expenses')
                ->modalSubheading('Record additional expenses with details.')
                ->form([
                    TextInput::make('total_expense')
                        ->label('Amount')
                        ->numeric()
                        ->required()
                        ->helperText('Enter the amount for the extra expense.'),
                    
                    Textarea::make('description')
                        ->label('Description')
                        ->required()
                        ->placeholder('Provide a brief description of the extra expense.')
                        ->rows(3),

                    FileUpload::make('travel_bill')
                        ->label('Images')
                        ->required()
                        ->multiple(),                        
                ])
                ->action(function (array $data): void {
                    // Save the extra expense data
                    TrainerVisit::create([
                        'total_expense' => $data['total_expense'],
                        'description' => $data['description'],
                        'user_id' => auth()->id(), // Assign the current user
                        'company_id' => auth()->user()->company_id, // Optional if company is relevant
                    ]);

                    // Notification for success
                    \Filament\Notifications\Notification::make()
                        ->title('Extra Expense Added')
                        ->success()
                        ->body('The extra expense has been successfully recorded.')
                        ->send();
                }),
        ];
    }



    public function getTabs(): array
    {
        $user = auth()->user();

        // Admin or accounts roles can see all records
        if (!$user->hasRole(['admin', 'accounts', 'accounts_head'])) {
            return [
                'all' => Tab::make('All Visits')
                    ->modifyQueryUsing(fn (Builder $query) => $query),

                'verified' => Tab::make('Verified Visits')
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('verify_status', 'verified'))
                    ->badgeColor('success'),

                'unverified' => Tab::make('Unverified Visits')
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('verify_status', 'unverified'))
                    ->badgeColor('danger'),

                    'approved' => Tab::make('approved')
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_status', 'approved')),

                    'pending' => Tab::make('pending')

            ];
        }else{

            return[
                'all' => Tab::make('All Visits')
                    ->modifyQueryUsing(fn (Builder $query) => $query),

                    'approved' => Tab::make('approved')
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_status', 'approved')),

                    'pending' => Tab::make('pending')
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_status', 'pending')),

             
            ];

        }

    }

        protected function accountsApprovalCount(): int
        {
            return TrainerVisitResource::getModel()::where('approval_status', 'approved')->count();
        }

        protected function accountsPendingCount(): int
        {
            return TrainerVisitResource::getModel()::where('approval_status', 'pending')->where('verify_status', 'verified')->count();
        }


    protected function getAllVisitsCount(): int
    {
        return TrainerVisitResource::getModel()::count();
    }

    protected function getVerifiedVisitsCount(): int
    {
        return TrainerVisitResource::getModel()::where('verify_status', 'verified')->count();
    }

    protected function getUnverifiedVisitsCount(): int
    {
        return TrainerVisitResource::getModel()::where('verify_status', 'unverified')->count();
    }

    protected function getUserVisitsCount(int $userId): int
    {
        return TrainerVisitResource::getModel()::where('user_id', $userId)->count();
    }

    protected function getUserVerifiedVisitsCount(int $userId): int
    {
        return TrainerVisitResource::getModel()::where('user_id', $userId)->where('verify_status', 'verified')->count();
    }

    protected function getUserUnverifiedVisitsCount(int $userId): int
    {
        return TrainerVisitResource::getModel()::where('user_id', $userId)->where('verify_status', 'unverified')->count();
    }


    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        // For admin, show all records
        if ($user->hasRole('admin')) {
            return TrainerVisit::query();
        }

        // For sales_operation, show records for their company
        if ($user->hasRole('sales_operation')) {
            return TrainerVisit::where('company_id', $user->company_id);
        }

        if ($user->hasRole('accounts_head')) {
            return TrainerVisit::where('verify_status', 'verified');
        }

        if ($user->hasRole('accounts')) {
            return TrainerVisit::where('verify_status', 'verified');
        }

        

        // For others, show only their own records
        return TrainerVisit::where('user_id', $user->id);
    }
}
