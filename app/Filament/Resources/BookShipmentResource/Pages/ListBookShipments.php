<?php

namespace App\Filament\Resources\BookShipmentResource\Pages;

use App\Filament\Resources\BookShipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookShipments extends ListRecords
{
    protected static string $resource = BookShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
