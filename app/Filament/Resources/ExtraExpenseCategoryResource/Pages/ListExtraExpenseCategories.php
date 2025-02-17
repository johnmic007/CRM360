<?php

namespace App\Filament\Resources\ExtraExpenseCategoryResource\Pages;

use App\Filament\Resources\ExtraExpenseCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExtraExpenseCategories extends ListRecords
{
    protected static string $resource = ExtraExpenseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
