<?php

namespace App\Filament\Resources\RevenewReportResource\Pages;

use App\Filament\Resources\RevenewReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRevenewReport extends EditRecord
{
    protected static string $resource = RevenewReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
