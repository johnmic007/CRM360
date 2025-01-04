<?php

namespace App\Filament\Resources\LeadStageReportResource\Pages;

use App\Filament\Resources\LeadStageReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLeadStageReport extends EditRecord
{
    protected static string $resource = LeadStageReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
