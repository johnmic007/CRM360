<?php

namespace App\Filament\Resources\BookShipmentResource\Pages;

use App\Filament\Resources\BookShipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListBookShipments extends ListRecords
{
    protected static string $resource = BookShipmentResource::class;

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery()
            ->whereHas('schoolBook'); // Only show schools with book shipments

        $user = Auth::user();

        // If the user has 'admin' or 'sales_operation' role, show all records
        if ($user->hasRole(['admin', 'head' , 'sales_operation'])) {
            return $query;
        }

        // Otherwise, only show records where `closed_by` is the logged-in user
        return $query->whereHas('bookShipments', function ($shipmentQuery) use ($user) {
            $shipmentQuery->where('closed_by', $user->id);
        });
    }

   
}
