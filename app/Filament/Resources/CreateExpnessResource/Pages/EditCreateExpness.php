<?php

namespace App\Filament\Resources\CreateExpnessResource\Pages;

use App\Filament\Resources\CreateExpnessResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCreateExpness extends EditRecord
{
    protected static string $resource = CreateExpnessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
