<?php

namespace App\Filament\Resources\ExtraExpensesResource\Pages;

use App\Filament\Resources\ExtraExpensesResource;
use App\Filament\Resources\ExtraExpensesResource\Widgets\ExpenseStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListExtraExpenses extends ListRecords
{
    protected static string $resource = ExtraExpensesResource::class;

    /**
     * Only records with travel_type = extra_expense in the table.
     */
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('travel_type', 'extra_expense');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


    protected function getHeaderWidgets(): array
    {
        return [
            ExpenseStats::class, // ✅ Add stats widget to the List Page
        ];
    }
}
