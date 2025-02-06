<?php

namespace App\Filament\Resources\UsersLeadStatusReportResource\Pages;

use App\Filament\Resources\UsersLeadStatusReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUsersLeadStatusReport extends EditRecord
{
    protected static string $resource = UsersLeadStatusReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
