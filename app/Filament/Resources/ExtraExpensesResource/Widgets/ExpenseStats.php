<?php

namespace App\Filament\Resources\ExtraExpensesResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Resources\ExtraExpensesResource\Pages\ListExtraExpenses;

class ExpenseStats extends BaseWidget
{
    use InteractsWithPageTable; // ✅ Allows widget to fetch live filtered table data.

    protected static ?string $pollingInterval = null; // ✅ No auto-refresh needed, updates on filter change.

    /**
     * ✅ Link this widget to the correct table page.
     */
    protected function getTablePage(): string
    {
        return ListExtraExpenses::class;
    }

    protected function getCards(): array
    {
        $query = $this->getPageTableQuery();

        return [
            Card::make('Total Expenses', "₹" . number_format($query->sum('total_expense'), 2))
                ->color('primary'),

            Card::make('Travel Expenses', "₹" . number_format(
                $query->where('category', 'Travel')->sum('total_expense'),
                2
            ))->color('info'),

            Card::make('Marketing Expenses', "₹" . number_format(
                $query->where('category', 'Marketing')->sum('total_expense'),
                2
            ))->color('success'),

            Card::make('Operations Expenses', "₹" . number_format(
                $query->where('category', 'Operations')->sum('total_expense'),
                2
            ))->color('warning'),
        ];
    }
}
