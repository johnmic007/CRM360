<?php

namespace App\Filament\Resources\VisitReportResource\Pages;

use App\Filament\Resources\VisitReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVisitReport extends EditRecord
{
    protected static string $resource = VisitReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
