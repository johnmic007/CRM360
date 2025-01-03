<?php

namespace App\Filament\Resources\SalesLeadStatusResource\Pages;

use App\Filament\Resources\SalesLeadStatusResource;
use App\Models\VisitEntry;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalesLeadStatuses extends ListRecords
{
    protected static string $resource = SalesLeadStatusResource::class;

  



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

    if ($user->roles()->where('name', 'sales_operation_head')->exists()) {
        return $query
            ->whereHas('user', fn($query) => $query->where('company_id', $user->company_id));
    }

    $subordinateIds = $user->getAllSubordinateIds();

    return $query
        ->whereIn('user_id', $subordinateIds);
}



}
