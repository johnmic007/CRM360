<?php

namespace App\Filament\Resources\WalletLogResource\Pages;

use App\Filament\Resources\WalletLogResource;
use App\Filament\Widgets\WalletBalanceWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;

class ListWalletLogs extends ListRecords
{
    protected static string $resource = WalletLogResource::class;



    protected function getTableQuery(): Builder
    {
        $userId = auth()->id();
        $companyId = $user->company_id ?? 0;
        $isAdminOrSales = auth()->user()->hasAnyRole(['admin', 'sales_operation','accounts_head' ]);

        return parent::getTableQuery()
            ->when(
                !$isAdminOrSales,
                fn (Builder $query) => $query->where('user_id', $userId), // Non-admins see their logs
                fn (Builder $query) => $query->where('company_id', $companyId) // Admins/Sales see company logs
            );
    }

    public function getTabs(): array
    {
        $userId = auth()->id();
        $companyId = $user->company_id ?? 0;
        $isAdminOrSales = auth()->user()->hasAnyRole(['admin', 'sales_operation','accounts_head' ]);

        return [
            'all' => Tab::make('All Logs')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->when(
                        !$isAdminOrSales,
                        fn ($q) => $q->where('user_id', $userId), // Non-admins see their logs
                        fn ($q) => $q->where('company_id', $companyId) // Admins/Sales see company logs
                    )
                ),

            'credit' => Tab::make('Credits')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->where('type', 'credit')
                          ->when(
                              !$isAdminOrSales,
                              fn ($q) => $q->where('user_id', $userId), // Non-admins see their logs
                              fn ($q) => $q->where('company_id', $companyId) // Admins/Sales see company logs
                          )
                )
                ->badge(fn () => $this->getLogTypeCount('credit', $userId, $companyId, $isAdminOrSales)),

            'debit' => Tab::make('Debits')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->where('type', 'debit')
                          ->when(
                              !$isAdminOrSales,
                              fn ($q) => $q->where('user_id', $userId), // Non-admins see their logs
                              fn ($q) => $q->where('company_id', $companyId) // Admins/Sales see company logs
                          )
                )
                ->badge(fn () => $this->getLogTypeCount('debit', $userId, $companyId, $isAdminOrSales)),
        ];
    }

    protected function getLogTypeCount(string $type, int $userId, ?int $companyId = null, bool $isAdminOrSales): int
    {
        return WalletLogResource::getEloquentQuery()
            ->where('type', $type)
            ->when(
                !$isAdminOrSales,
                fn ($query) => $query->where('user_id', $userId), // Non-admins see their logs
                fn ($query) => $query->where('company_id', $companyId) // Admins/Sales see company logs
            )
            ->count();
    }




    // public  function getHeaderWidgets(): array
    // {
    //     return [
    //         WalletBalanceWidget::class,
    //     ];
    // }
}
