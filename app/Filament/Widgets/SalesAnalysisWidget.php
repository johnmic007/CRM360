<?php

namespace App\Filament\Widgets;

use App\Models\InvoiceLog;
use App\Models\Invoice;
use Filament\Widgets\LineChartWidget;

class SalesAnalysisWidget extends LineChartWidget
{
    public static function canView(): bool
    {
        return auth()->user()->hasRole(['admin', 'head', 'sales']);
    }

    protected static ?int $sort = 3;



    

    protected static ?string $heading = 'Sales and Invoice Analysis';

    public ?string $filter = 'month'; // Default filter

    // Define available filters
    public function getFilters(): ?array
    {
        return [
            'day' => 'Today',
            'week' => 'This Week',
            'month' => 'This Month',
        ];
    }

    protected function getData(): array
    {
        // Resolve filter dates
        $startDate = now()->startOf($this->filter);
        $endDate = now()->endOf($this->filter);

        // Fetch payment trends
        $paymentLogs = InvoiceLog::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($log) {
                return $log->created_at->toDateString(); // Group by date
            })
            ->map(function ($logs) {
                return $logs->sum('paid_amount'); // Sum paid amounts for each date
            });

        // Initialize datasets for invoices
        $invoiceData = Invoice::whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($invoice) {
                return $invoice->created_at->toDateString(); // Group by date
            });

        // Prepare totals for invoices
        $totalInvoiced = [];
        $totalPaid = [];
        $totalDue = [];
        $dates = $paymentLogs->keys()->merge($invoiceData->keys())->unique()->sort();

        foreach ($dates as $date) {
            $dailyInvoices = $invoiceData->get($date, collect());
            $totalInvoiced[] = $dailyInvoices->sum('total_amount');
            $totalPaid[] = $dailyInvoices->sum('paid');
            $totalDue[] = $dailyInvoices->sum('due_amount');
        }

        return [
            'datasets' => [
                // [
                //     'label' => 'Payments Received',
                //     'data' => $dates->map(fn($date) => $paymentLogs->get($date, 0)),
                //     'borderColor' => '#4CAF50', // Green for payments
                //     'backgroundColor' => 'rgba(76, 175, 80, 0.2)',
                // ],
                [
                    'label' => 'Total Invoiced Amount',
                    'data' => $totalInvoiced,
                    'borderColor' => '#2196F3', // Blue for invoiced amount
                    'backgroundColor' => 'rgba(33, 150, 243, 0.2)',
                ],
                [
                    'label' => 'Total Paid Amount',
                    'data' => $totalPaid,
                    'borderColor' => '#FFC107', // Yellow for paid amount
                    'backgroundColor' => 'rgba(255, 193, 7, 0.2)',
                ],
                // [
                //     'label' => 'Total Due Amount',
                //     'data' => $totalDue,
                //     'borderColor' => '#F44336', // Red for due amount
                //     'backgroundColor' => 'rgba(244, 67, 54, 0.2)',
                // ],
            ],
            'labels' => $dates->values(),
        ];
    }
}
