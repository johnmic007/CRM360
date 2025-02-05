<?php

namespace App\Filament\Resources\SummaryExpenseReportResource\Pages;

use App\Filament\Resources\SummaryExpenseReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSummaryExpenseReport extends EditRecord
{
    protected static string $resource = SummaryExpenseReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
