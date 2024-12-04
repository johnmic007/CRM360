<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

use Illuminate\Database\Eloquent\Builder;


class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

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


        if ($user->roles()->where('name', 'admin')->exists()) {
            return $query; // Show all users for admins
        }


        if ($user->roles()->where('name', 'sales')->exists()) {
            return $query->where('company_id', $user->company_id);
        }


        // Fetch all subordinate IDs for the logged-in user
        $subordinateIds = $user->getAllSubordinateIds();

        // Always apply the filter for subordinates
        return $query->whereIn('id', $subordinateIds);
    }
}
