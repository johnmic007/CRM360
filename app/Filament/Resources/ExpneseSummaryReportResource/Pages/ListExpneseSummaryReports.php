<?php

namespace App\Filament\Resources\ExpneseSummaryReportResource\Pages;

use App\Filament\Resources\ExpneseSummaryReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpneseSummaryReports extends ListRecords
{
    protected static string $resource = ExpneseSummaryReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
