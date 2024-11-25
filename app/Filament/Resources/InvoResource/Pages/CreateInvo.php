<?php

namespace App\Filament\Resources\InvoResource\Pages;

use App\Filament\Resources\InvoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateInvo extends CreateRecord
{
    protected static string $resource = InvoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Preselect the school_id if it's provided in the query string
        if ($schoolId = request()->query('school_id')) {
            $data['school_id'] = $schoolId;
        }

        return $data;
    }

    protected function getFormSchema(): array
    {
        return parent::getFormSchema();
    }
    
}
