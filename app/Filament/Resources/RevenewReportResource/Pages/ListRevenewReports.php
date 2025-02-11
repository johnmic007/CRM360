<?php

namespace App\Filament\Resources\RevenewReportResource\Pages;

use App\Filament\Resources\RevenewReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;


class ListRevenewReports extends ListRecords
{
    protected static string $resource = RevenewReportResource::class;

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery(); // Get the default query

        $user = auth()->user();

        // Allow all reports for admin role
        if ($user->roles()->whereIn('name', ['admin','sales_operation_head'])->exists()) {
            return $query;
        }

        // Show reports for the logged-in user's company for specific roles
        // if ($user->roles()->whereIn('name', ['sales_operation_head', 'head', 'sales_operation', 'company'])->exists()) {
        //     return $query->where('company_id', $user->company_id);
        // }

        // Fetch subordinate user IDs for other roles
        $subordinateIds = $user->getAllSubordinateIds();

        // Show only reports for the subordinates
        return $query->whereIn('closed_by', $subordinateIds);
    }
}
