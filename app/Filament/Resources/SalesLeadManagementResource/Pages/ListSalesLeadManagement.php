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

    public function getTabs(): array
    {
        $userCompanyId = auth()->user()->company_id;

        return [
            'all' => Tab::make('All Leads')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('company_id', $userCompanyId)),

            'deal_won' => Tab::make('Deal Won')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'deal_won')
                          ->where('company_id', $userCompanyId))
                ->badge(fn () => $this->getDealWonCount($userCompanyId)),

            'deal_lost' => Tab::make('Deal Lost')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'deal_lost')
                          ->where('company_id', $userCompanyId))
                ->badge(fn () => $this->getDealLostCount($userCompanyId)),

            'demo_completed' => Tab::make('Demo Completed')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'Demo Completed')
                          ->where('company_id', $userCompanyId))
                ->badge(fn () => $this->getDemoCompletedCount($userCompanyId)),

            // 'demo_rescheduled' => Tab::make('Demo Rescheduled')
            //     ->modifyQueryUsing(fn (Builder $query) => 
            //         $query->where('status', 'Demo reschedule')
            //               ->where('company_id', $userCompanyId))
            //     ->badge(fn () => $this->getDemoRescheduledCount($userCompanyId)),

            // 'lead_re_engaged' => Tab::make('Lead Re-engaged')
            //     ->modifyQueryUsing(fn (Builder $query) => 
            //         $query->where('status', 'Lead Re-engaged')
            //               ->where('company_id', $userCompanyId))
            //     ->badge(fn () => $this->getLeadReEngagedCount($userCompanyId)),

            'school_nurturing' => Tab::make('School Nurturing')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'School Nurturing')
                          ->where('company_id', $userCompanyId))
                ->badge(fn () => $this->getSchoolNurturingCount($userCompanyId)),
        ];
    }

    protected function getDealWonCount(int $companyId): int
    {
        return SalesLeadManagementResource::getEloquentQuery()
            ->where('status', 'deal_won')
            ->where('company_id', $companyId)
            ->count();
    }

    protected function getDealLostCount(int $companyId): int
    {
        return SalesLeadManagementResource::getEloquentQuery()
            ->where('status', 'deal_lost')
            ->where('company_id', $companyId)
            ->count();
    }

    protected function getDemoCompletedCount(int $companyId): int
    {
        return SalesLeadManagementResource::getEloquentQuery()
            ->where('status', 'Demo Completed')
            ->where('company_id', $companyId)
            ->count();
    }

    protected function getDemoRescheduledCount(int $companyId): int
    {
        return SalesLeadManagementResource::getEloquentQuery()
            ->where('status', 'Demo reschedule')
            ->where('company_id', $companyId)
            ->count();
    }

    protected function getLeadReEngagedCount(int $companyId): int
    {
        return SalesLeadManagementResource::getEloquentQuery()
            ->where('status', 'Lead Re-engaged')
            ->where('company_id', $companyId)
            ->count();
    }

    protected function getSchoolNurturingCount(int $companyId): int
    {
        return SalesLeadManagementResource::getEloquentQuery()
            ->where('status', 'School Nurturing')
            ->where('company_id', $companyId)
            ->count();
    }
}
