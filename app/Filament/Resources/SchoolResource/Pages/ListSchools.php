<?php

namespace App\Filament\Resources\SchoolResource\Pages;

use App\Filament\Resources\SchoolResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions;


class ListSchools extends ListRecords
{
    protected static string $resource = SchoolResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Schools')
                ->modifyQueryUsing(fn (Builder $query) => $query) // Show all schools
                ->badge(fn () => $this->getAllSchoolsCount()), // Badge with total count

            'demo_scheduled' => Tab::make('Demo Scheduled Schools')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('process_status', 'Demo Scheduled')) // Filter schools with "Demo Scheduled" process status
                ->badge(fn () => $this->getDemoScheduledSchoolsCount()), // Badge with filtered count
        ];
    }

    /**
     * Get the count of all schools.
     */
    protected function getAllSchoolsCount(): int
    {
        return SchoolResource::getEloquentQuery()->count();
    }

    /**
     * Get the count of schools with the "Demo Scheduled" process status.
     */
    protected function getDemoScheduledSchoolsCount(): int
    {
        return SchoolResource::getEloquentQuery()
            ->where('process_status', 'Demo Scheduled')
            ->count();
    }
}
