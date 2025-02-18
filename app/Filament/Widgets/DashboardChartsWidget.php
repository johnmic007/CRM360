<?php

namespace App\Filament\Widgets;

use Filament\Widgets\BarChartWidget;
use App\Models\Invoice;
use App\Models\TrainerVisit;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardChartsWidget extends BarChartWidget
{
    protected static ?string $heading = 'Monthly Financial Overview';
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 7;

    /**
     * ✅ Define Filters (Date Range)
     */
    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'last7days' => 'Last 7 Days',
            'thisMonth' => 'This Month',
            'last3months' => 'Last 3 Months',
            'last6months' => 'Last 6 Months',
            'thisYear' => 'This Year',
        ];
    }

    /**
     * ✅ Fetch Data for the Chart with Applied Filters
     */
    protected function getData(): array
    {
        $authUser = Auth::user();
        $subordinateIds = $this->getSubordinateIds($authUser);

        // ✅ Apply Date Filter
        $dateRange = $this->applyFilter($this->filter);

        // ✅ Fetch Data with Filters
        $monthlyClosed = Invoice::whereIn('closed_by', $subordinateIds)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select(DB::raw("DATE_FORMAT(created_at, '%b') as month"), DB::raw("SUM(total_amount) as total_closed"))
            ->groupBy('month')
            ->orderBy(DB::raw("STR_TO_DATE(month, '%b')"), 'ASC')
            ->pluck('total_closed', 'month')->toArray();

        $monthlyPaid = Invoice::whereIn('closed_by', $subordinateIds)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select(DB::raw("DATE_FORMAT(created_at, '%b') as month"), DB::raw("SUM(paid) as total_paid"))
            ->groupBy('month')
            ->orderBy(DB::raw("STR_TO_DATE(month, '%b')"), 'ASC')
            ->pluck('total_paid', 'month')->toArray();

        $monthlyExpenses = TrainerVisit::whereIn('user_id', $subordinateIds)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->select(DB::raw("DATE_FORMAT(created_at, '%b') as month"), DB::raw("SUM(total_expense) as total_expense"))
            ->groupBy('month')
            ->orderBy(DB::raw("STR_TO_DATE(month, '%b')"), 'ASC')
            ->pluck('total_expense', 'month')->toArray();

        // ✅ Calculate Profit Percentage
        $profitData = [];
        foreach ($monthlyPaid as $month => $paid) {
            $expenses = $monthlyExpenses[$month] ?? 0;
            $profitPercentage = ($paid != 0) ? (($paid - $expenses) / abs($paid)) * 100 : 0;
            $profitData[$month] = $profitPercentage;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Closed',
                    'data' => array_values($monthlyClosed),
                    'backgroundColor' => 'rgba(75, 192, 192, 0.6)',
                ],
                [
                    'label' => 'Total Paid',
                    'data' => array_values($monthlyPaid),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                ],
                [
                    'label' => 'Total Expenses',
                    'data' => array_values($monthlyExpenses),
                    'backgroundColor' => 'rgba(255, 99, 132, 0.6)',
                ],
                [
                    'label' => 'Profit %',
                    'data' => array_values($profitData),
                    'backgroundColor' => 'rgba(255, 206, 86, 0.6)',
                    'yAxisID' => 'profitAxis'
                ],
            ],
            'labels' => array_keys($monthlyClosed),
        ];
    }

    /**
     * ✅ Apply Date Filter Logic
     */
    protected function applyFilter(?string $filter): array
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
            case 'last3months':
                $start = now()->subMonths(3)->startOfMonth();
                break;
            case 'last6months':
                $start = now()->subMonths(6)->startOfMonth();
                break;
            case 'thisYear':
                $start = now()->startOfYear();
                $end = now()->endOfYear();
                break;
            default:
                return ['start' => now()->subMonths(6)->toDateString(), 'end' => now()->toDateString()];
        }

        return [
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
        ];
    }

    /**
     * Fetch Subordinate IDs based on Role
     */
    private function getSubordinateIds($user)
    {
        if ($user->hasRole('admin')) {
            return User::pluck('id')->toArray();
        } elseif ($user->hasRole('sales_operation_head')) {
            return User::where('company_id', $user->company_id)->pluck('id')->toArray();
        } else {
            $subordinateIds = $user->getAllSubordinateIds();
            if (!in_array($user->id, $subordinateIds)) {
                $subordinateIds[] = $user->id;
            }
            return $subordinateIds;
        }
    }
}
