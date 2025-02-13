<?php

namespace App\Filament\Resources\LeadStageReportResource\Pages;

use App\Filament\Resources\LeadStageReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLeadStageReports extends ListRecords
{
    protected static string $resource = LeadStageReportResource::class;



    protected function getHeaderActions(): array
{
    return [
        // School Nurturing Count
        Actions\Action::make('school_nurturing_count')
            ->label(function () {
                $query = $this->getTableQuery();
                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }
                $count = $query->where('status', 'School Nurturing')->count();
                return "School Nurturing: {$count}";
            })
            ->color('primary')
            ->icon('heroicon-o-academic-cap')
            ->disabled(), // Display-only action

            Actions\Action::make('demo_reschedule_count')
            ->label(function () {
                $query = $this->getTableQuery();
                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }
                $count = $query->where('status', 'Demo Reschedule')->count();
                return "Demo Reschedule: {$count}";
            })
            ->color('warning')
            ->icon('heroicon-o-calendar')
            ->disabled(), 

        // Demo Completed Count
        Actions\Action::make('demo_completed_count')
            ->label(function () {
                $query = $this->getTableQuery();
                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }
                $count = $query->where('status', 'Demo Completed')->count();
                return "Demo Completed: {$count}";
            })
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->disabled(), // Display-only action

        // Deal Won Count
        Actions\Action::make('deal_won_count')
            ->label(function () {
                $query = $this->getTableQuery();
                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }
                $count = $query->where('status', 'deal_won')->count();
                return "Deal Won: {$count}";
            })
            ->color('success')
            ->icon('heroicon-o-trophy')
            ->disabled(), // Display-only action

        // Deal Lost Count
        Actions\Action::make('deal_lost_count')
            ->label(function () {
                $query = $this->getTableQuery();
                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }
                $count = $query->where('status', 'deal_lost')->count();
                return "Deal Lost: {$count}";
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

    // Allow all reports for admin role
    if ($user->roles()->whereIn('name', ['admin'])->exists()) {
        return $query;
    }

    // Show reports for specific roles without restrictions
    if ($user->roles()->whereIn('name', ['sales_operation_head', 'head', 'sales_operation', 'company'])->exists()) {
        return $query;
    }

    // Fetch subordinate user IDs
    $subordinateIds = $user->getAllSubordinateIds();
    $allowedUserIds = array_merge([$user->id], $subordinateIds);

    // Filter query to show only records where leadStatuses has an entry created by user or subordinates
    return $query->whereHas('leadStatuses', function ($q) use ($allowedUserIds) {
        $q->whereIn('created_by', $allowedUserIds);
    });
}

  
}
