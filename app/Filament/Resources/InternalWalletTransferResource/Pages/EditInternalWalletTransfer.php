<?php

namespace App\Filament\Resources\InternalWalletTransferResource\Pages;

use App\Filament\Resources\InternalWalletTransferResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInternalWalletTransfer extends EditRecord
{
    protected static string $resource = InternalWalletTransferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
