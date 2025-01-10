<?php

namespace App\Filament\Resources\WalletLogResource\Pages;

use App\Filament\Resources\WalletLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWalletLog extends EditRecord
{
    protected static string $resource = WalletLogResource::class;

    
    protected function getFormActions(): array
    {
        return [];
    }
    
}
