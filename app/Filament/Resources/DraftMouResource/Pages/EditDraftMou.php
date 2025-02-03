<?php

namespace App\Filament\Resources\DraftMouResource\Pages;

use App\Filament\Resources\DraftMouResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDraftMou extends EditRecord
{
    protected static string $resource = DraftMouResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
