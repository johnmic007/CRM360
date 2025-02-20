<?php

namespace App\Filament\Resources\InternalWalletTransferResource\Pages;

use App\Filament\Resources\InternalWalletTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListInternalWalletTransfers extends ListRecords
{
    protected static string $resource = InternalWalletTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function applyFiltersToTableQuery(Builder $query): Builder
    {
        $userId = Auth::id();

        // If the user is an admin, sales operation head, or accounts head, show all transfers
        if (Auth::user()->hasAnyRole(['admin', 'sales_operation_head', 'accounts_head'])) {
            return $query;
        }

        // Otherwise, filter the query to only show transfers where the user is involved
        return $query->where(function ($q) use ($userId) {
            $q->where('from_user_id', $userId)
              ->orWhere('to_user_id', $userId);
        });
    }
}
