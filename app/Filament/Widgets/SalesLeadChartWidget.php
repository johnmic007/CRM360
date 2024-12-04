<?php



namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\SalesLeadManagement;
use Illuminate\Support\Facades\Auth;

class SalesLeadChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Sales Lead Overview';
    protected static ?int $sort = 3;

    // Configure Chart Type
    protected function getType(): string
    {
        return 'bar'; // Change to 'line', 'pie', etc. if needed
    }

    // Provide Data for the Chart
    protected function getData(): array
    {
        $userCompanyId = Auth::user()->company_id;

        // Initialize query for the user's company
        $query = SalesLeadManagement::query()->where('company_id', $userCompanyId);

        // Apply date filtering if applicable
        if ($this->filter) {
            $dateRange = $this->applyFilter($this->filter);
            if ($dateRange) {
                $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            }
        }

        // Define statuses
        $statuses = [
            'Deal Won' => 'deal_won',
            'Deal Lost' => 'deal_lost',
            'Demo Completed' => 'Demo Completed',
            'Demo Rescheduled' => 'Demo reschedule',
            'Lead Re-engaged' => 'Lead Re-engaged',
            'School Nurturing' => 'School Nurturing',
        ];

        $data = [];
        foreach ($statuses as $label => $status) {
            $data[] = $query->clone()->where('status', $status)->count();
        }

        return [
            'labels' => array_keys($statuses),
            'datasets' => [
                [
                    'label' => 'Number of Leads',
                    'data' => $data,
                    'backgroundColor' => [
                        '#4caf50', // Green
                        '#f44336', // Red
                        '#2196f3', // Blue
                        '#ff9800', // Orange
                        '#9c27b0', // Purple
                        '#607d8b', // Grey
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
                $end = now()->endOfMonth();
                break;
            case 'custom':
                // Custom logic, will need to handle custom date range if provided
                return null; // You can add custom logic for date range inputs if needed
            default:
                return null; // No filter applied
        }

        return [
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
        ];
    }

    // Optional: Date filter logic
    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'last7days' => 'Last 7 Days',
            'thisMonth' => 'This Month',
            'custom' => 'Custom Date Range',
        ];
    }
}
