<?php

namespace App\Filament\Resources\TopUpResource\Pages;

use App\Filament\Resources\TopUpResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTopUps extends ListRecords
{
    protected static string $resource = TopUpResource::class;

   
    

    public function getTabs(): array
    {
        $user = auth()->user();

        // Admin or accounts roles can see all records
        if ($user->hasRole(['admin', 'accounts', 'accounts_head'])) {
            return [
                'all' => Tab::make('All Top-Ups')
                    ->modifyQueryUsing(fn (Builder $query) => $query)
                    ->badge(fn () => $this->getAllTopUpsCount()),

                'low_balance' => Tab::make('Low Wallet Balance')
                    ->modifyQueryUsing(fn (Builder $query) => $query->where('wallet_balance', '<', 5000))
                    ->badge(fn () => $this->getLowBalanceCount())
                    ->badgeColor('danger'),

                'recent_topups' => Tab::make('Recent Top-Ups')
                    ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', '>=', now()->subDays(7)))
                    ->badge(fn () => $this->getRecentTopUpsCount())
                    ->badgeColor('success'),
            ];
        }

        // Other roles only see filtered records
        return [
            'all' => Tab::make('All Top-Ups')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', $user->id))
                ->badge(fn () => $this->getUserTopUpsCount($user->id)),

            'low_balance' => Tab::make('Low Wallet Balance')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', $user->id)->where('wallet_balance', '<', 5000))
                ->badge(fn () => $this->getUserLowBalanceCount($user->id))
                ->badgeColor('danger'),
        ];
    }

    protected function getAllTopUpsCount(): int
    {
        return TopUpResource::getModel()::count();
    }

    protected function getLowBalanceCount(): int
    {
        return TopUpResource::getModel()::where('wallet_balance', '<', 5000)->count();
    }

    protected function getRecentTopUpsCount(): int
    {
        return TopUpResource::getModel()::whereDate('created_at', '>=', now()->subDays(7))->count();
    }

    protected function getUserTopUpsCount(int $userId): int
    {
        return TopUpResource::getModel()::where('user_id', $userId)->count();
    }

    protected function getUserLowBalanceCount(int $userId): int
    {
        return TopUpResource::getModel()::where('user_id', $userId)->where('wallet_balance', '<', 5000)->count();
    }
}
