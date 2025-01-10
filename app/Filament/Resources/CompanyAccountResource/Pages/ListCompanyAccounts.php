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

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


    protected function getTableQuery(): Builder
    {
        return User::query()->whereHas('roles', function ($query) {
            $query->where('name', 'accounts_head');
        });
    }
    
}
