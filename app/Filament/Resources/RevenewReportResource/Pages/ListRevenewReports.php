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
    
        // Allow all reports for admin and sales_operation_head roles
        if ($user->roles()->whereIn('name', ['admin','head', 'sales_operation', 'sales_operation_head'])->exists()) {
            return $query;
        }
    
        // Fetch subordinate user IDs & include the current user's ID
        $subordinateIds = $user->getAllSubordinateIds();
        $allowedUserIds = array_merge([$user->id], $subordinateIds);
    
        // Show only reports for the logged-in user and their subordinates
        return $query->whereIn('closed_by', $allowedUserIds);
    }
    
}
