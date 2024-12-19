<?php

namespace App\Filament\Resources\SalesLeadManagementResource\Pages;

use App\Filament\Resources\SalesLeadManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalesLeadManagement extends EditRecord
{
    protected static string $resource = SalesLeadManagementResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }
}
