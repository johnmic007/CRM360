<?php

namespace App\Filament\Resources\ExpensesReportResource\Pages;

use App\Filament\Resources\ExpensesReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;


class ListExpensesReports extends ListRecords
{
    protected static string $resource = ExpensesReportResource::class;



    protected function getHeaderActions(): array
{
    return [
        // Total Expenses
        Actions\Action::make('total_expenses')
            ->label(function () {
                $query = $this->getTableQuery();
                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }
                $totalExpenses = $query->sum('total_expense');
                return "Total Expenses: ₹" . number_format($totalExpenses, 2);
            })
            ->color('success')
            ->icon('heroicon-o-calculator')
            ->disabled(), // Display-only action

        // Total Requests
        Actions\Action::make('total_requests')
            ->label(function () {
                $query = $this->getTableQuery();
                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }
                $totalRequests = $query->count();
                return "Total Requests: {$totalRequests}";
            })
            ->color('primary')
            ->icon('heroicon-o-document-text')
            ->disabled(), // Display-only action

        // Approved Expenses
        Actions\Action::make('approved_expenses')
            ->label(function () {
                $query = $this->getTableQuery();
                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }
                $approvedExpenses = $query->where('approval_status', 'approved')->sum('total_expense');
                return "Approved Expenses: ₹" . number_format($approvedExpenses, 2);
            })
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->disabled(), // Display-only action

        // Rejected Expenses
        Actions\Action::make('rejected_expenses')
            ->label(function () {
                $query = $this->getTableQuery();
                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }
                $rejectedExpenses = $query->where('approval_status', 'rejected')->sum('total_expense');
                return "Rejected Expenses: ₹" . number_format($rejectedExpenses, 2);
            })
            ->color('danger')
            ->icon('heroicon-o-x-circle')
            ->disabled(), // Display-only action
    ];
}



    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery(); // Get the default query

        $user = auth()->user();

        // Allow all reports for admin and accounts_head roles
        if ($user->roles()->whereIn('name', ['admin'])->exists()) {
            return $query;
        }

        // Show reports for the logged-in user's company for sales_operation role
        if ($user->roles()->where('name', ['sales_operation_head' ,'head' , 'sales_operation'])->exists()) {
            return $query->where('company_id', $user->company_id);
        }

        // Fetch subordinate user IDs for other roles
        $subordinateIds = $user->getAllSubordinateIds();

        // Show only reports for the subordinates
        return $query->whereIn('user_id', $subordinateIds);
    }
  
    
}
