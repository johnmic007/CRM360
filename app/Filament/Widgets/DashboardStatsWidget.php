<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\BarChartWidget; // Import BarChartWidget
use App\Models\Invoice;
use App\Models\User;
use App\Models\TrainerVisit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardStatsWidget extends BaseWidget
{
    protected function getCards(): array
    {
        $authUser = Auth::user();
        Log::info('DashboardStatsWidget: Logged-in User:', ['id' => $authUser->id, 'name' => $authUser->name]);

        $user = User::find($authUser->id);
        if (!$user) {
            Log::error('DashboardStatsWidget: User instance not found.');
            return [];
        }

        if ($user->hasRole('admin')) {
            $subordinateIds = User::pluck('id')->toArray();
        } elseif ($user->hasRole('sales_operation_head')) {
            $subordinateIds = User::where('company_id', $user->company_id)->pluck('id')->toArray();
        } else {
            $subordinateIds = $user->getAllSubordinateIds();
            if (!in_array($user->id, $subordinateIds)) {
                $subordinateIds[] = $user->id;
            }
        }

        $totalAmount = Invoice::whereIn('closed_by', $subordinateIds)->sum('total_amount');
        $totalPaid = Invoice::whereIn('closed_by', $subordinateIds)->sum('paid');
        $totalExpenses = TrainerVisit::whereIn('user_id', $subordinateIds)->sum('total_expense');

        $profitPercentage = ($totalPaid != 0) ? (($totalPaid - $totalExpenses) / abs($totalPaid)) * 100 : 0;
        $profitColor = ($profitPercentage < 0) ? 'danger' : 'success';

        $cards = [];

        if (!$user->hasRole('admin') && !$user->hasRole('sales_operation_head')) {
            $cards[] = Card::make('Wallet Balance', '₹' . number_format($user->wallet_balance ?? 0, 2))
                ->description('Available balance in your wallet')
                ->icon('heroicon-o-currency-rupee')
                ->color($user->wallet_balance > 0 ? 'success' : 'danger');
        }

        $cards[] = Card::make('Total Amount Closed', '₹' . number_format($totalAmount, 2))
            ->description('Total amount closed by you and your team')
            ->icon('heroicon-o-currency-rupee')
            ->color('success');

        $cards[] = Card::make('Total Paid Amount', '₹' . number_format($totalPaid, 2))
            ->description('Total amount collected by you and your team')
            ->icon('heroicon-o-currency-rupee')
            ->color('success');

        $cards[] = Card::make('Total Trainer Visit Expenses', '₹' . number_format($totalExpenses, 2))
            ->description('Total expenses spent on trainer visits')
            ->icon('heroicon-o-currency-rupee')
            ->color('warning');

        $cards[] = Card::make('Profit Percentage', number_format($profitPercentage, 2) . '%')
            ->description('Profit based on Total Paid and Total Expenses')
            ->icon('heroicon-o-arrow-trending-up')
            ->color($profitColor);

        return $cards;
    }

    protected function getCharts(): array
    {
        $authUser = Auth::user();
        $user = User::find($authUser->id);
        $subordinateIds = ($user->hasRole('admin')) ? User::pluck('id')->toArray() : $user->getAllSubordinateIds();

        if (!in_array($user->id, $subordinateIds)) {
            $subordinateIds[] = $user->id;
        }

        $monthlyData = Invoice::whereIn('closed_by', $subordinateIds)
            ->select(DB::raw("DATE_FORMAT(created_at, '%b') as month"), 
                     DB::raw("SUM(total_amount) as total_closed"), 
                     DB::raw("SUM(paid) as total_paid"))
            ->groupBy('month')
            ->orderBy(DB::raw("STR_TO_DATE(month, '%b')"), 'ASC')
            ->pluck('total_closed', 'month')->toArray();

        $expensesData = TrainerVisit::whereIn('user_id', $subordinateIds)
            ->select(DB::raw("DATE_FORMAT(created_at, '%b') as month"), 
                     DB::raw("SUM(total_expense) as total_expense"))
            ->groupBy('month')
            ->orderBy(DB::raw("STR_TO_DATE(month, '%b')"), 'ASC')
            ->pluck('total_expense', 'month')->toArray();

        $profitData = [];
        foreach ($monthlyData as $month => $closed) {
            $paid = $monthlyData[$month] ?? 0;
            $expenses = $expensesData[$month] ?? 0;
            $profitPercentage = ($paid != 0) ? (($paid - $expenses) / abs($paid)) * 100 : 0;
            $profitData[$month] = $profitPercentage;
        }

        return [
            new class extends BarChartWidget {
                protected static ?string $heading = 'Total Amount Closed';
                protected function getData(): array {
                    return [
                        'datasets' => [
                            [
                                'label' => 'Closed Amount',
                                'data' => array_values($GLOBALS['monthlyData']),
                            ],
                        ],
                        'labels' => array_keys($GLOBALS['monthlyData']),
                    ];
                }
            },

            new class extends BarChartWidget {
                protected static ?string $heading = 'Total Paid Amount';
                protected function getData(): array {
                    return [
                        'datasets' => [
                            [
                                'label' => 'Paid Amount',
                                'data' => array_values($GLOBALS['monthlyData']),
                            ],
                        ],
                        'labels' => array_keys($GLOBALS['monthlyData']),
                    ];
                }
            },

            new class extends BarChartWidget {
                protected static ?string $heading = 'Trainer Visit Expenses';
                protected function getData(): array {
                    return [
                        'datasets' => [
                            [
                                'label' => 'Expenses',
                                'data' => array_values($GLOBALS['expensesData']),
                            ],
                        ],
                        'labels' => array_keys($GLOBALS['expensesData']),
                    ];
                }
            },

            new class extends BarChartWidget {
                protected static ?string $heading = 'Profit Percentage';
                protected function getData(): array {
                    return [
                        'datasets' => [
                            [
                                'label' => 'Profit %',
                                'data' => array_values($GLOBALS['profitData']),
                            ],
                        ],
                        'labels' => array_keys($GLOBALS['profitData']),
                    ];
                }
            },
        ];
    }
}
