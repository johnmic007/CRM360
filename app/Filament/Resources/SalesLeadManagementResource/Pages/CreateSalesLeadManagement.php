<?php

namespace App\Filament\Resources\SalesLeadManagementResource\Pages;

use App\Filament\Resources\SalesLeadManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesLeadManagement extends CreateRecord
{
    protected static string $resource = SalesLeadManagementResource::class;


    protected function mutateFormDataBeforeSave(array $data): array
{
    if (!auth()->user()->hasRole('admin')) {
        $data['allocated_to'] = auth()->id(); // Force the current user's ID
    }

    return $data;
}

}
