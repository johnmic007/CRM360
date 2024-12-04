<?php

namespace App\Filament\Resources\SchoolBooksUpdateResource\Pages;

use App\Filament\Resources\SchoolBooksUpdateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolBooksUpdate extends EditRecord
{
    protected static string $resource = SchoolBooksUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
