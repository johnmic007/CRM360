<?php

namespace App\Filament\Resources\CompanyAccountResource\Pages;

use App\Filament\Resources\CompanyAccountResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class ListCompanyAccounts extends ListRecords
{
    protected static string $resource = CompanyAccountResource::class;




    protected function getTableQuery(): Builder
{
    $user = auth()->user(); // Get the authenticated user

    return User::query()->whereHas('roles', function ($query) use ($user) {
        $query->where('name', 'accounts_head');

        // Apply company filter only if $user is not null
        if ($user) {
            $query->where('company_id', $user->company_id);
        }
    });
}


}
