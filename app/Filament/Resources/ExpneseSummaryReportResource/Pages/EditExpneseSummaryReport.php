<?php

namespace App\Filament\Resources\ExpneseSummaryReportResource\Pages;

use App\Filament\Resources\ExpneseSummaryReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpneseSummaryReport extends EditRecord
{
    protected static string $resource = ExpneseSummaryReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
