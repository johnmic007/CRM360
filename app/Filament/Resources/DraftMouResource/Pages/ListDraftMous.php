<?php

namespace App\Filament\Resources\DraftMouResource\Pages;

use App\Filament\Resources\DraftMouResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDraftMous extends ListRecords
{
    protected static string $resource = DraftMouResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
