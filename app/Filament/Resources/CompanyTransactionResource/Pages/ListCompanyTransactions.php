<?php

namespace App\Filament\Resources\CompanyTransactionResource\Pages;

use App\Filament\Resources\CompanyTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompanyTransactions extends ListRecords
{
    protected static string $resource = CompanyTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
