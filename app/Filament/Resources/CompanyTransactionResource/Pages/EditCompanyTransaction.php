<?php

namespace App\Filament\Resources\CompanyTransactionResource\Pages;

use App\Filament\Resources\CompanyTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompanyTransaction extends EditRecord
{
    protected static string $resource = CompanyTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
