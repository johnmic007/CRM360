<?php

namespace App\Filament\Widgets;

use App\Models\SalesLeadManagement;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class DealWonLineChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Deal Status Chart';
    protected static ?int $sort = 2;  // Ensure it comes after WalletBalanceWidget

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'last7days' => 'Last 7 Days',
            'thisMonth' => 'This Month',
            'lastMonth' => 'Last Month',
        ];
    }

    protected function getData(): array
    {
        // Get the current filter from the widget
        $filter = $this->filter;

        // Determine the date range based on the filter
        $dateRange = $this->applyFilter($filter);
        $userCompanyId = Auth::user()->company_id;

        // Query the database for sales data within the date range
        $query = SalesLeadManagement::query()
            ->where('company_id', $userCompanyId);

        // Apply the date range filter
        if ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
        }

        // Group by date (daily trend)
        $trendData = $query->selectRaw('DATE(created_at) as date, 
                                        COUNT(CASE WHEN status = "deal_won" THEN 1 END) as deal_won_count,
                                        COUNT(CASE WHEN status = "deal_lost" THEN 1 END) as deal_lost_count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = $trendData->pluck('date')->toArray();
        $dealWonCounts = $trendData->pluck('deal_won_count')->toArray();
        $dealLostCounts = $trendData->pluck('deal_lost_count')->toArray();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Deals Won',
                    'data' => $dealWonCounts,
                    'borderColor' => '#4caf50',
                    'backgroundColor' => 'rgba(76, 175, 80, 0.2)',
                    'fill' => true,
                    'tension' => 0.3,  // Smooth curve
                ],
                [
                    'label' => 'Deals Lost',
                    'data' => $dealLostCounts,
                    'borderColor' => '#f44336',
                    'backgroundColor' => 'rgba(244, 67, 54, 0.2)',
                    'fill' => true,
                    'tension' => 0.3,  // Smooth curve
                ],
            ],
            'options' => [
                'responsive' => true, // Make the chart responsive
                'maintainAspectRatio' => false, // Allow chart to fill the container
                'scales' => [
                    'x' => [
                        'beginAtZero' => true, // Ensure x-axis starts from zero
                    ],
                ],
            ],
        ];
    }

    /**
     * Apply the selected filter and return the start and end date for the query.
     *
     * @param string|null $filter
     * @return array|null
     */
    protected function applyFilter(?string $filter): ?array
    {
        $start = now()->startOfDay();
        $end = now()->endOfDay();

        switch ($filter) {
            case 'today':
                break;
            case 'last7days':
                $start = now()->subDays(7)->startOfDay();
                break;
            case 'thisMonth':
                $start = now()->startOfMonth();
                break;
            case 'lastMonth':
                $start = now()->subMonth()->startOfMonth();
                $end = now()->subMonth()->endOfMonth();
                break;
            default:
                return null; // No filter applied
        }

        return [
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
