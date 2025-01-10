<?php

namespace App\Filament\Resources\AccountsClosingResource\Pages;

use App\Filament\Resources\AccountsClosingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountsClosing extends EditRecord
{
    protected static string $resource = AccountsClosingResource::class;

   
    protected function getFormActions(): array
    {
        return [];
    }
}
