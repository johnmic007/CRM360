<?php

namespace App\Filament\Resources\VisitReportResource\Pages;

use App\Filament\Resources\VisitReportResource;
use App\Models\VisitEntry;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\ButtonAction;


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

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();

        // Base query to include only records where start_time is not null
        $query = VisitEntry::query()
            ->whereNotNull('start_time') // Only include records with a non-null start_time
            ->with(['user', 'trainerVisit', 'leadStatuses']);

        // Fetch visits based on user role
        if ($user->roles()->where('name', 'admin')->exists() || $user->roles()->where('name', 'accounts_head')->exists()) {
            return $query;
        }

        if ($user->roles()->where('name', 'sales_operation')->exists()) {
            return $query
                ->whereHas('user', fn($query) => $query->where('company_id', $user->company_id));
        }

        $subordinateIds = $user->getAllSubordinateIds();

        return $query
            ->whereIn('user_id', $subordinateIds);
    }
}
