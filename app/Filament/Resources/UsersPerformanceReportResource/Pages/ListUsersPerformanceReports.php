<?php

namespace App\Filament\Resources\UsersPerformanceReportResource\Pages;

use App\Filament\Resources\UsersPerformanceReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;


class ListUsersPerformanceReports extends ListRecords
{
    protected static string $resource = UsersPerformanceReportResource::class;

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery(); // Get the default query

        $user = auth()->user();

        // Allow all reports for admin role
        if ($user->roles()->whereIn('name', ['admin','sales_operation_head'])->exists()) {
            return $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'admin');
            });        }

        // Show reports for the logged-in user's company for specific roles
        // if ($user->roles()->whereIn('name', ['sales_operation_head', 'head', 'sales_operation', 'company'])->exists()) {
        //     return $query->where('company_id', $user->company_id);
        // }

        // Fetch subordinate user IDs for other roles
        $subordinateIds = $user->getAllSubordinateIds();

        // Show only reports for the subordinates
        return $query->whereIn('id', $subordinateIds)
        ->whereDoesntHave('roles', function ($q) {
            $q->where('name', 'admin');
        });
    
    }
}
