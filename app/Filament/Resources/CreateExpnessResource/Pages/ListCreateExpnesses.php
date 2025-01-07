<?php

namespace App\Filament\Resources\CreateExpnessResource\Pages;

use App\Filament\Resources\CreateExpnessResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;


class ListCreateExpnesses extends ListRecords
{
    protected static string $resource = CreateExpnessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Create New Expnesses'),
        ];
    }



protected function getTableQuery(): Builder
{
    $query = parent::getTableQuery(); // Get the default query
    $user = auth()->user(); // Get the authenticated user

  

    // Show records where `created_by` exists for all other roles
    return $query->whereNotNull('created_by');
}

}
