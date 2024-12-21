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
                        ->multiple()                        
                        ->image(),
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


    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        // For admin, show all records
        if ($user->hasRole('admin')) {
            return TrainerVisit::query();
        }

        // For sales, show records for their company
        if ($user->hasRole('sales')) {
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
