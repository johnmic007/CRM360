<?php

namespace App\Filament\Resources\ItemsResource\Pages;

use App\Filament\Resources\ItemsResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateItems extends CreateRecord
{
    protected static string $resource = ItemsResource::class;
}
