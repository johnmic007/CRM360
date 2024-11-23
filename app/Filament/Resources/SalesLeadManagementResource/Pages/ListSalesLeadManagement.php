<?php

namespace App\Filament\Resources\SalesLeadManagementResource\Pages;

use App\Filament\Resources\SalesLeadManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalesLeadManagement extends ListRecords
{
    protected static string $resource = SalesLeadManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
