<?php

namespace App\Filament\Resources\UsersPerformanceReportResource\Pages;

use App\Filament\Resources\UsersPerformanceReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUsersPerformanceReport extends EditRecord
{
    protected static string $resource = UsersPerformanceReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
