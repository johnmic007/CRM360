<?php

namespace App\Filament\Resources\SchoolimportResource\Pages;

use App\Filament\Resources\SchoolimportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolimport extends EditRecord
{
    protected static string $resource = SchoolimportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
