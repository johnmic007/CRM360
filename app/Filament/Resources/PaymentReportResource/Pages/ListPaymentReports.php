<?php

namespace App\Filament\Resources\PaymentReportResource\Pages;

use App\Filament\Resources\PaymentReportResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPaymentReports extends ListRecords
{
    protected static string $resource = PaymentReportResource::class;

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery(); // Get the default query
    
        $user = auth()->user();
    
        // Ensure we only show records where type = 'payment'
        $query->where('type', 'payment');
    
        // Allow all reports for admin and sales_operation_head roles
        if ($user->roles()->whereIn('name', ['admin', 'head', 'sales_operation_head'])->exists()) {
            return $query;
        }
    
        // Fetch subordinate user IDs & include the current user's ID
        $subordinateIds = $user->getAllSubordinateIds();
        $allowedUserIds = array_merge([$user->id], $subordinateIds);
    
        // Correct filtering by `invoice.closed_by`
        return $query->whereHas('invoice', function ($query) use ($allowedUserIds) {
            $query->whereIn('closed_by', $allowedUserIds);
        });
    }
    
}
