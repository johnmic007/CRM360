<?php

namespace App\Filament\Resources\BoardResource\Pages;

use App\Filament\Resources\BoardResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBoard extends CreateRecord
{
    protected static string $resource = BoardResource::class;
}
