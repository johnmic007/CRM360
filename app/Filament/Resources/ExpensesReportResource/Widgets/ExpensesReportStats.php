<?php

namespace App\Filament\Resources\ExpensesReportResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Resources\ExpensesReportResource\Pages\ListExpensesReports;

class ExpensesReportStats extends BaseWidget
{
    use InteractsWithPageTable; // ✅ Allows the widget to interact with table filters.

    protected static ?string $pollingInterval = null; // ✅ No auto-refresh needed.

    /**
     * ✅ Link this widget to the correct table page.
     */
    protected function getTablePage(): string
    {
        return ListExpensesReports::class;
    }

    protected function getCards(): array
    {
        $query = $this->getPageTableQuery(); // ✅ Fetch dynamically filtered query.

        return [
            Card::make('💰 Total Expenses', "₹" . number_format($query->sum('total_expense'), 2))
                ->color('success')
                ->icon('heroicon-o-calculator')
                ->extraAttributes([
                    'class' => 'text-2xl font-bold p-4 rounded-xl shadow-lg bg-gradient-to-r from-green-500 to-green-700 text-white',
                ]),

            Card::make('📄 Total Requests', number_format($query->count(), 0, '.', ','))
                ->color('primary')
                ->icon('heroicon-o-document-text')
                ->extraAttributes([
                    'class' => 'text-lg font-semibold p-4 rounded-lg shadow-md',
                ]),

            Card::make('✅ Approved Expenses', "₹" . number_format(
                $query->where('approval_status', 'approved')->sum('total_expense'), 2
            ))->color('success')
              ->icon('heroicon-o-check-circle')
              ->extraAttributes([
                  'class' => 'text-lg font-semibold p-4 rounded-lg shadow-md',
              ]),

            // Card::make('⏳ Pending Expenses', "₹" . number_format(
            //     $query->where('approval_status', 'pending')->sum('total_expense'), 2
            // ))->color('warning')
            //   ->icon('heroicon-o-clock')
            //   ->extraAttributes([
            //       'class' => 'text-lg font-semibold p-4 rounded-lg shadow-md',
            //   ]),

            // Card::make('❌ Rejected Expenses', "₹" . number_format(
            //     $query->where('approval_status', 'rejected')->sum('total_expense'), 2
            // ))->color('danger')
            //   ->icon('heroicon-o-x-circle')
            //   ->extraAttributes([
            //       'class' => 'text-lg font-semibold p-4 rounded-lg shadow-md',
            //   ]),

            // Card::make('🚗 Total Cars Used', number_format(
            //     $query->where('travel_mode', 'car')->count(), 0, '.', ','
            // ))->color('info')
            //   ->icon('heroicon-o-truck')
            //   ->extraAttributes([
            //       'class' => 'text-lg font-semibold p-4 rounded-lg shadow-md',
            //   ]),

            // Card::make('📊 Avg. Expense per Request', "₹" . number_format($query->avg('total_expense') ?? 0, 2))
            //     ->color('gray')
            //     ->icon('heroicon-o-chart-bar')
            //     ->extraAttributes([
            //         'class' => 'text-lg font-semibold p-4 rounded-lg shadow-md bg-gray-100',
            //     ]),
        ];
    }
}
