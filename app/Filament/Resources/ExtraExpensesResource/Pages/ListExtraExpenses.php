<?php

namespace App\Filament\Resources\ExtraExpensesResource\Pages;

use App\Filament\Resources\ExtraExpensesResource;
use App\Filament\Resources\ExtraExpensesResource\Widgets\ExpenseStats;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Illuminate\Database\Eloquent\Builder;

class ListExtraExpenses extends ListRecords
{
    use ExposesTableToWidgets; // ✅ Allows widgets to interact with table filters.

    protected static string $resource = ExtraExpensesResource::class;

    /**
     * ✅ Apply filters using `getTableQuery()`
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
            ExpenseStats::class, // ✅ Now the widget dynamically updates based on filters
        ];
    }
}
