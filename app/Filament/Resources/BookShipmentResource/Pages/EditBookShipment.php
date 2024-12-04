<?php

namespace App\Filament\Resources\BookShipmentResource\Pages;

use App\Filament\Resources\BookShipmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBookShipment extends EditRecord
{
    protected static string $resource = BookShipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
