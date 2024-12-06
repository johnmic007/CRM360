<?php

namespace App\Filament\Resources\TrainerVisitResource\Pages;

use App\Filament\Resources\TrainerVisitResource;
use App\Models\TrainerVisit;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;


class ListTrainerVisits extends ListRecords
{
    protected static string $resource = TrainerVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
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

        // For others, show only their own records
        return TrainerVisit::where('user_id', $user->id);
    }
}
