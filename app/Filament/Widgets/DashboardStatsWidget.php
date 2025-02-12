<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Import Log Facade

class DashboardStatsWidget extends BaseWidget
{
    protected function getCards(): array
    {
        $authUser = Auth::user(); // Get the logged-in user
        Log::info('DashboardStatsWidget: Logged-in User:', ['id' => $authUser->id, 'name' => $authUser->name]);

        // Retrieve the full user instance (ensure relationships are loaded correctly)
        $user = User::find($authUser->id);

        // Ensure the user instance exists before proceeding
        if (!$user) {
            Log::error('DashboardStatsWidget: User instance not found.');
            return [
                Card::make('Wallet Balance', '0.00')
                    ->description('Available balance in your wallet')
                    ->icon('heroicon-o-currency-rupee')
                    ->color('danger'),
                Card::make('Total Amount Closed', '0.00')
                    ->description('No data available')
                    ->icon('heroicon-o-currency-rupee')
                    ->color('warning'),
                Card::make('Total Paid Amount', '0.00')
                    ->description('No data available')
                    ->icon('heroicon-o-currency-rupee')
                    ->color('warning'),
            ];
        }

        // ✅ 1. Wallet Balance
        $walletBalance = $user->wallet_balance ?? 0;
        Log::info('DashboardStatsWidget: Wallet Balance:', ['amount' => $walletBalance]);

        if ($user->hasRole('admin')) {
            // Admin should see all data
            Log::info('DashboardStatsWidget: Admin detected, fetching data for all users.');
            $subordinateIds = User::pluck('id')->toArray(); // Fetch all user IDs
        } elseif ($user->hasRole('sales_operation_head')) {
            // Fetch all users with the same company ID
            $companyId = $user->company_id;
            Log::info('DashboardStatsWidget: sales_operation_head detected, fetching data for company:', ['company_id' => $companyId]);
            $subordinateIds = User::where('company_id', $companyId)->pluck('id')->toArray();
        } else {
            // Non-admins see their own data + subordinates
            $subordinateIds = $user->getAllSubordinateIds();
            if (!in_array($user->id, $subordinateIds)) {
                $subordinateIds[] = $user->id;
            }
        }

        // ✅ 2. Get all subordinates' IDs & Ensure Logged-in User's ID is included
       // $subordinateIds = $user->getAllSubordinateIds();
        // array_unshift($subordinateIds, $user->id); // Ensure the logged-in user's ID is first in the array

        Log::info('DashboardStatsWidget: Subordinate IDs including logged-in user:', $subordinateIds);

        // ✅ 3. Fetch total amount from invoices closed by logged-in user & subordinates
        $totalAmountQuery = Invoice::whereIn('closed_by', $subordinateIds);
        $totalAmount = $totalAmountQuery->sum('total_amount');

        Log::info('DashboardStatsWidget: Total Amount Query:', ['sql' => $totalAmountQuery->toSql(), 'bindings' => $totalAmountQuery->getBindings()]);
        Log::info('DashboardStatsWidget: Total Amount Closed:', ['amount' => $totalAmount]);

        // ✅ 4. Fetch total paid amount from invoices closed by logged-in user & subordinates
        $totalPaidQuery = Invoice::whereIn('closed_by', $subordinateIds);
        $totalPaid = $totalPaidQuery->sum('paid');

        Log::info('DashboardStatsWidget: Total Paid Query:', ['sql' => $totalPaidQuery->toSql(), 'bindings' => $totalPaidQuery->getBindings()]);
        Log::info('DashboardStatsWidget: Total Paid Amount:', ['amount' => $totalPaid]);

        return [
            // Wallet Balance Card
            Card::make('Wallet Balance', '₹' . number_format($walletBalance, 2))
                ->description('Available balance in your wallet')
                ->icon('heroicon-o-currency-rupee')
                ->chart([1,2,3,4,3,2,1])
                ->color($walletBalance > 0 ? 'success' : 'danger'),

            // Total Amount Closed Card
            Card::make('Total Amount Closed', '₹' . number_format($totalAmount, 2))
                ->description('Total amount closed by you and your team')
                ->icon('heroicon-o-currency-rupee')
                ->chart([1,2,3,4,5,6,7])
                ->color('success'),

            // Total Paid Amount Card
            Card::make('Total Paid Amount', '₹' . number_format($totalPaid, 2))
                ->description('Total amount collected by you and your team')
                ->icon('heroicon-o-currency-rupee')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
        ];
    }
}
