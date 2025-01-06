<?php

namespace App\Filament\Resources\VisitReportResource\Pages;

use App\Filament\Resources\VisitReportResource;
use App\Models\VisitEntry;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\ButtonAction;
use Illuminate\Database\Eloquent\Builder;



class ListVisitReports extends ListRecords
{
    protected static string $resource = VisitReportResource::class;



    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('visit_counts')
                ->label(function () {
                    $query = $this->getTableQuery();
                    if (method_exists($this, 'applyFiltersToTableQuery')) {
                        $this->applyFiltersToTableQuery($query);
                    }
                    $visitCount = $query->count();
                    return "Visits: {$visitCount}";
                })
                ->color('success')
                ->icon('heroicon-o-briefcase') // Example: Briefcase icon
                ->disabled(), // Display-only action

            Actions\Action::make('total_leads')
                ->label(function () {
                    $query = $this->getTableQuery();
                    if (method_exists($this, 'applyFiltersToTableQuery')) {
                        $this->applyFiltersToTableQuery($query);
                    }
                    $leadCount = $query->withCount('leadStatuses')->get()->sum('lead_statuses_count');
                    return "Leads: {$leadCount}";
                })
                ->color('primary')
                ->icon('heroicon-o-user-group') // Example: User group icon
                ->disabled(), // Display-only action

            Actions\Action::make('potential_meets')
                ->label(function () {
                    $query = $this->getTableQuery();
                    if (method_exists($this, 'applyFiltersToTableQuery')) {
                        $this->applyFiltersToTableQuery($query);
                    }
                    $potentialCount = $query->withSum('leadStatuses', 'potential_meet')->get()->sum('lead_statuses_sum_potential_meet');
                    return "Potential Meets: {$potentialCount}";
                })
                ->color('warning')
                ->icon('heroicon-o-hand-raised') // Example: Handshake icon
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
