<?php

namespace App\Filament\Resources\ClosureAmountResource\Pages;

use App\Filament\Resources\ClosureAmountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClosureAmount extends EditRecord
{
    protected static string $resource = ClosureAmountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
