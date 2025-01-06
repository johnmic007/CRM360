<?php

namespace App\Filament\Resources\ExpensesReportResource\Pages;

use App\Filament\Resources\ExpensesReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpensesReport extends EditRecord
{
    protected static string $resource = ExpensesReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
