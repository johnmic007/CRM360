<?php

namespace App\Filament\Resources\InvoResource\Pages;

use App\Filament\Resources\InvoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvo extends EditRecord
{
    protected static string $resource = InvoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
