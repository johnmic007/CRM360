<?php

namespace App\Filament\Resources\SalesLeadManagementResource\Pages;

use App\Filament\Resources\SalesLeadManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSalesLeadManagement extends ListRecords
{
    protected static string $resource = SalesLeadManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        return parent::getTableQuery()
            ->when(
                !$user->hasRole('admin'),
                fn(Builder $query) => $query->where(function ($subQuery) use ($user) {
                    $accessibleUserIds = $user->getAllSubordinateIds();
                    $accessibleUserIds[] = $user->id; // Include the user's own ID
                    $subQuery->whereIn('allocated_to', $accessibleUserIds);
                })
            );
    }

    public function getTabs(): array
    {
        $user = auth()->user();
        $userCompanyId = $user->company_id ?? 0;


        if ($user->hasRole('admin')) {
            return [
                'all' => Tab::make('All Leads')
                    ->modifyQueryUsing(fn(Builder $query) => $query),

                'deal_won' => Tab::make('Deal Won')
                    ->modifyQueryUsing(
                        fn(Builder $query) =>
                        $query->where('status', 'deal_won')
                        
                    ),

                    'deal_lost' => Tab::make('Deal Lost')
                    ->modifyQueryUsing(
                        fn(Builder $query) =>
                        $query->where('status', 'deal_lost')
                        
                    )
            ];
        }

        // Fetch subordinates' IDs
        $accessibleUserIds = $user->getAllSubordinateIds();
        $accessibleUserIds[] = $user->id;

        return [
            'all' => Tab::make('All Leads')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereIn('allocated_to', $accessibleUserIds)
                        ->where('company_id', $userCompanyId)
                ),

            'deal_won' => Tab::make('Deal Won')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereIn('allocated_to', $accessibleUserIds)
                        ->where('status', 'deal_won')
                        ->where('company_id', $userCompanyId)
                )
                ->badge(fn() => $this->getDealWonCount($accessibleUserIds, $userCompanyId)),

            'deal_lost' => Tab::make('Deal Lost')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereIn('allocated_to', $accessibleUserIds)
                        ->where('status', 'deal_lost')
                        ->where('company_id', $userCompanyId)
                )
                ->badge(fn() => $this->getDealLostCount($accessibleUserIds, $userCompanyId)),

            'demo_completed' => Tab::make('Demo Completed')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereIn('allocated_to', $accessibleUserIds)
                        ->where('status', 'Demo Completed')
                        ->where('company_id', $userCompanyId)
                )
                ->badge(fn() => $this->getDemoCompletedCount($accessibleUserIds, $userCompanyId)),

            'school_nurturing' => Tab::make('School Nurturing')
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereIn('allocated_to', $accessibleUserIds)
                        ->where('status', 'School Nurturing')
                        ->where('company_id', $userCompanyId)
                )
                ->badge(fn() => $this->getSchoolNurturingCount($accessibleUserIds, $userCompanyId)),
        ];
    }

    protected function getDealWonCount(array $accessibleUserIds, int $companyId): int
    {
        return SalesLeadManagementResource::getEloquentQuery()
            ->whereIn('allocated_to', $accessibleUserIds)
            ->where('status', 'deal_won')
            ->where('company_id', $companyId)
            ->count();
    }

    protected function getDealLostCount(array $accessibleUserIds, int $companyId): int
    {
        return SalesLeadManagementResource::getEloquentQuery()
            ->whereIn('allocated_to', $accessibleUserIds)
            ->where('status', 'deal_lost')
            ->where('company_id', $companyId)
            ->count();
    }

    protected function getDemoCompletedCount(array $accessibleUserIds, int $companyId): int
    {
        return SalesLeadManagementResource::getEloquentQuery()
            ->whereIn('allocated_to', $accessibleUserIds)
            ->where('status', 'Demo Completed')
            ->where('company_id', $companyId)
            ->count();
    }

    protected function getSchoolNurturingCount(array $accessibleUserIds, int $companyId): int
    {
        return SalesLeadManagementResource::getEloquentQuery()
            ->whereIn('allocated_to', $accessibleUserIds)
            ->where('status', 'School Nurturing')
            ->where('company_id', $companyId)
            ->count();
    }
}
