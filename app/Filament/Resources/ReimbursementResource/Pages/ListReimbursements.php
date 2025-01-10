<?php

namespace App\Filament\Resources\ReimbursementResource\Pages;

use App\Filament\Resources\ReimbursementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListReimbursements extends ListRecords
{
    protected static string $resource = ReimbursementResource::class;

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        $user = Auth::user();

        if ($user->hasAnyRole(['admin', 'accounts_head'])) {
            // For admin and accounts_head, filter by their company
            return $query->where('company_id', $user->company_id);
        } else {
            // For other users, filter by their user_id
            return $query->where('user_id', $user->id);
        }
    }
}
