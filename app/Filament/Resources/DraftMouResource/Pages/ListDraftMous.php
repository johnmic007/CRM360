<?php

namespace App\Filament\Resources\DraftMouResource\Pages;

use App\Filament\Resources\DraftMouResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListDraftMous extends ListRecords
{
    protected static string $resource = DraftMouResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
{

    $query = parent::getTableQuery(); // Get the default query
    $user = auth()->user();

    // Allow all reports for admin role
    if ($user->roles()->whereIn('name', ['admin'])->exists()) {
        return $query;
    }

    // Show reports for the logged-in user's company for specific roles
    if ($user->roles()->whereIn('name', ['sales_operation_head', 'head', 'sales_operation', 'company'])->exists()) {
        return $query->where('company_id', $user->company_id);
    }
}
}
