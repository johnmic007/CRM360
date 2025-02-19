<?php

namespace App\Filament\Resources\InternalWalletTransferResource\Pages;

use App\Filament\Resources\InternalWalletTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInternalWalletTransfers extends ListRecords
{
    protected static string $resource = InternalWalletTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
