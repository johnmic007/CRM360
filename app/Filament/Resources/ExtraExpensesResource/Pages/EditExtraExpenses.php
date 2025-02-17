<?php

namespace App\Filament\Resources\ExtraExpensesResource\Pages;

use App\Filament\Resources\ExtraExpensesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExtraExpenses extends EditRecord
{
    protected static string $resource = ExtraExpensesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
