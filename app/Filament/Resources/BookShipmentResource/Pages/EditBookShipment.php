<?php

namespace App\Filament\Resources\BookShipmentResource\Pages;

use App\Filament\Resources\BookShipmentResource;
use Filament\Resources\Pages\EditRecord;

class EditBookShipment extends EditRecord
{
    protected static string $resource = BookShipmentResource::class;

    public function getHeading(): string
    {
        return __('Shipment Tracking'); // Change the heading
    }

    protected function getFormActions(): array
    {
        return []; // Remove Save & Cancel buttons
    }
}
