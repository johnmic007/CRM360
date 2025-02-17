<?php

namespace App\Filament\Resources\ExtraExpenseCategoryResource\Pages;

use App\Filament\Resources\ExtraExpenseCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExtraExpenseCategory extends EditRecord
{
    protected static string $resource = ExtraExpenseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
