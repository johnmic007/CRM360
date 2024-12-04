<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class WalletBalanceWidget extends BaseWidget
{

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Auth::user();
        $walletBalance = $user->wallet_balance ?? 0;

        return [
            Stat::make('Wallet Balance', '₹' . number_format($walletBalance, 2))
                ->description('Available balance in your wallet')
                ->color($walletBalance > 0 ? 'success' : 'danger')
                ->icon($walletBalance > 0 ? 'heroicon-o-currency-rupee' : 'heroicon-o-exclamation-circle'),
        ];
    }
}
