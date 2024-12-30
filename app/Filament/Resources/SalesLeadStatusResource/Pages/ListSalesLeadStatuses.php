<?php

namespace App\Filament\Resources\SalesLeadStatusResource\Pages;

use App\Filament\Resources\SalesLeadStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSalesLeadStatuses extends ListRecords
{
    protected static string $resource = SalesLeadStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
