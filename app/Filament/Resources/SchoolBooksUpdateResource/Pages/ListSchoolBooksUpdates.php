<?php

namespace App\Filament\Resources\SchoolBooksUpdateResource\Pages;

use App\Filament\Resources\SchoolBooksUpdateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSchoolBooksUpdates extends ListRecords
{
    protected static string $resource = SchoolBooksUpdateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
