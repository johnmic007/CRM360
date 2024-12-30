<?php

namespace App\Filament\Resources\SalesLeadStatusResource\Pages;

use App\Filament\Resources\SalesLeadStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalesLeadStatus extends EditRecord
{
    protected static string $resource = SalesLeadStatusResource::class;

  


    protected function getFormActions(): array
    {
        return []; // Remove all default form actions
    }

    
}
