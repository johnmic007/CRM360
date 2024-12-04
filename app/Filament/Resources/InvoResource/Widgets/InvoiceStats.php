<?php

namespace App\Filament\Resources\InvoResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget\Card;

use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class InvoiceStats extends BaseWidget
{
    protected function getCards(): array
    {
        $totalInvoices = Invoice::count();
        $totalPaid = Invoice::sum('paid');
        $totalDue = Invoice::sum(DB::raw('total_amount - paid'));

        return [
            Card::make('Total Invoices', $totalInvoices)
                ->description('All issued invoices')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),

            Card::make('Total Paid', '$' . number_format($totalPaid, 2))
                ->description('Total amount collected')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('success'),

            Card::make('Total Due', '$' . number_format($totalDue, 2))
                ->description('Amount remaining')
                ->descriptionIcon('heroicon-o-clock')
                ->color('danger'),
        ];
    }
}
