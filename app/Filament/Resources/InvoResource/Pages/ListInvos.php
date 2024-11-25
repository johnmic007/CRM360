<?php

namespace App\Filament\Resources\InvoResource\Pages;

use App\Filament\Resources\InvoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvos extends ListRecords
{
    protected static string $resource = InvoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
