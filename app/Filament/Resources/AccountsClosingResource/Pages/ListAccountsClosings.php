<?php

namespace App\Filament\Resources\AccountsClosingResource\Pages;

use App\Filament\Resources\AccountsClosingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAccountsClosings extends ListRecords
{
    protected static string $resource = AccountsClosingResource::class;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->whereNotNull('balance')
            ->where('credit_type', 'accounts topup');
    }
}
