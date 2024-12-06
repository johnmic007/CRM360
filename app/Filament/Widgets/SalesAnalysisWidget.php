<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\InvoiceLog;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Facades\DB;

class SalesAnalysisWidget extends LineChartWidget
{
    protected static ?string $heading = 'Sales Analysis';

    public ?string $filter = 'month'; // Default time filter (e.g., month, week)

    protected function getData(): array
    {
        // Filter dates
        $startDate = now()->startOf($this->filter);
        $endDate = now()->endOf($this->filter);

        // Invoice Data (Summary)
        $invoiceSummary = Invoice::query()
            ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->get();

        // Payment Trends
        $paymentTrends = InvoiceLog::query()
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total_paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->get()
            ->pluck('total_paid', 'date');

        return [
            'datasets' => [
                [
                    'label' => 'Payments Received',
                    'data' => $paymentTrends->values(),
                    'borderColor' => '#4CAF50', // Green for payments
                    'backgroundColor' => 'rgba(76, 175, 80, 0.2)',
                ],
            ],
            'labels' => $paymentTrends->keys(),
        ];
    }
}
