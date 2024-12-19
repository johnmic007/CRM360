<?php

namespace App\Filament\Resources\SchoolimportResource\Pages;

use App\Filament\Imports\SchoolImporter;
use App\Filament\Resources\SchoolimportResource;
use Filament\Resources\Pages\ListRecords;

use App\Models\District;
use App\Models\Block;
use Filament\Actions\ImportAction;

class ListSchoolimports extends ListRecords
{
    protected static string $resource = SchoolimportResource::class;

   

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()->label('Create Student'),
            ImportAction::make()
                ->importer(SchoolImporter::class)->color('success'),
                
        ];
    }
}
