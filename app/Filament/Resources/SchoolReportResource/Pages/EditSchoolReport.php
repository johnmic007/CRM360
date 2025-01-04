<?php

namespace App\Filament\Resources\SchoolReportResource\Pages;

use App\Filament\Resources\SchoolReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSchoolReport extends EditRecord
{
    protected static string $resource = SchoolReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
