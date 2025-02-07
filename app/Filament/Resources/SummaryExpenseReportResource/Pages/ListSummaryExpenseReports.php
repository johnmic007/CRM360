<?php

namespace App\Filament\Resources\SummaryExpenseReportResource\Pages;

use App\Filament\Resources\SummaryExpenseReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListSummaryExpenseReports extends ListRecords
{
    protected static string $resource = SummaryExpenseReportResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\Action::make('total_cash_in_hand')
            ->label(function () {
                $query = $this->getTableQuery();

                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }

                $totalCashInHand = $query
                    ->join('users', 'trainer_visits.user_id', '=', 'users.id')
                    ->whereDate('trainer_visits.created_at', '>=', now()->subMonth()) // ✅ Explicitly reference trainer_visits.created_at
                    ->sum('users.wallet_balance');

                return "Total Cash in Hand: ₹" . number_format($totalCashInHand, 2);
            })
            ->color('success')
            ->icon('heroicon-o-currency-rupee')
            ->disabled(), // Display-only action

            // ✅ Display Total Requests dynamically
        Actions\Action::make('total_requests')
        ->label(function () {
            $query = $this->getTableQuery();

            if (method_exists($this, 'applyFiltersToTableQuery')) {
                $this->applyFiltersToTableQuery($query);
            }

            // Compute Total Requests Correctly
            $totalRequests = $query->count();  // ✅ Correct count query

            return "Total Requests: " . number_format($totalRequests);
        })
        ->color('info')
        ->icon('heroicon-o-calculator')
        ->disabled(),


        // ✅ Display Total Expense dynamically
        Actions\Action::make('total_expense')
            ->label(function () {
                $query = $this->getTableQuery();

                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }

                // Compute Total Expense Correctly
                $totalExpense = $query->sum('trainer_visits.total_expense');  // ✅ Correct SUM query

                return "Total Expense: ₹" . number_format($totalExpense, 2);
            })
            ->color('warning')
            ->icon('heroicon-o-currency-rupee')
            ->disabled(),

            Actions\Action::make('total_travel_expense')
            ->label(function () {
                $query = $this->getTableQuery();

                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }

                $totalTravelExpense = $query->sum('trainer_visits.travel_expense');

                return "Total Travel Expense: ₹" . number_format($totalTravelExpense, 2);
            })
            ->color('primary')
            ->icon('heroicon-o-map')
            ->disabled(),

        Actions\Action::make('total_food_expense')
            ->label(function () {
                $query = $this->getTableQuery();

                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }

                $totalFoodExpense = $query->sum('trainer_visits.food_expense');

                return "Total Food Expense: ₹" . number_format($totalFoodExpense, 2);
            })
            ->color('red')
            ->icon('heroicon-o-fire')
            ->disabled(),

        Actions\Action::make('total_extra_expense')
            ->label(function () {
                $query = $this->getTableQuery();

                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }

                $totalExtraExpense = $query->where('trainer_visits.travel_type', 'extra_expense')
                    ->sum('trainer_visits.total_expense');

                return "Total Extra Expense: ₹" . number_format($totalExtraExpense, 2);
            })
            ->color('purple')
            ->icon('heroicon-o-plus')
            ->disabled(),

        Actions\Action::make('total_verified_expense')
            ->label(function () {
                $query = $this->getTableQuery();

                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }

                $totalVerifiedExpense = $query->where('trainer_visits.verify_status', 'verified')
                    ->sum('trainer_visits.total_expense');

                return "Total Verified Expense: ₹" . number_format($totalVerifiedExpense, 2);
            })
            ->color('green')
            ->icon('heroicon-o-check-circle')
            ->disabled(),

        Actions\Action::make('total_approved_expense')
            ->label(function () {
                $query = $this->getTableQuery();

                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }

                $totalApprovedExpense = $query->where('trainer_visits.approval_status', 'approved')
                    ->sum('trainer_visits.total_expense');

                return "Total Approved Expense: ₹" . number_format($totalApprovedExpense, 2);
            })
            ->color('blue')
            ->icon('heroicon-o-check')
            ->disabled(),

        Actions\Action::make('total_average_expense')
            ->label(function () {
                $query = $this->getTableQuery();

                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }

                $totalAverageExpense = $query->avg('trainer_visits.total_expense');

                return "Average Expense: ₹" . number_format($totalAverageExpense, 2);
            })
            ->color('gray')
            ->icon('heroicon-o-chart-bar')
            ->disabled(),


        ];
    }


    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if (method_exists($this, 'applyFiltersToTableQuery')) {
            $this->applyFiltersToTableQuery($query);
        }

        return $query;
    }
}
