<?php

namespace App\Filament\Resources\SchoolResource\Pages;

use App\Filament\Resources\SchoolResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions;

use App\Models\District;
use App\Models\Block;
use Konnco\FilamentImport\Actions\ImportAction;
use Konnco\FilamentImport\ImportField;
use Illuminate\Support\Str;


class ListSchools extends ListRecords
{
    protected static string $resource = SchoolResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }



    protected function getActions(): array
    {
        return [
            // Other actions like create can go here, if you want them:
            // Actions\CreateAction::make(),

            ImportAction::make()
                ->fields([
                    ImportField::make('district')
                        ->label('District')
                        ->required()
                        ->mutateBeforeCreate(fn($value) => ucfirst(strtolower($value))),

                    ImportField::make('block_name')
                        ->label('Block Name')
                        ->required(),

                    ImportField::make('school_name')
                        ->label('School Name')
                        ->required(),

                    ImportField::make('school_address')
                        ->label('School Address'),

                    ImportField::make('pincode')
                        ->label('Pincode'),
                ])
                ->mutateRowBeforeCreate(function ($row) {
                    // Ensure District entry exists
                    $district = District::firstOrCreate(['name' => $row['district']]);

                    // Ensure Block entry exists with the found District
                    $block = Block::firstOrCreate(
                        ['name' => $row['block_name'], 'district_id' => $district->id]
                    );

                    // Return the row data plus the block_id
                    return array_merge($row, ['block_id' => $block->id]);
                })
                ->massCreate(false)
                ->disableCreateAnother()
        ];
    }

    // public function getTabs(): array
    // {
    //     return [
    //         'all' => Tab::make('All Schools')
    //             ->modifyQueryUsing(fn (Builder $query) => $query) // Show all schools
    //             ->badge(fn () => $this->getAllSchoolsCount()), // Badge with total count

    //         'demo_scheduled' => Tab::make('Demo Scheduled Schools')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('process_status', 'Demo Scheduled')) // Filter schools with "Demo Scheduled" process status
    //             ->badge(fn () => $this->getDemoScheduledSchoolsCount()), // Badge with filtered count


    //         'deal_won' => Tab::make('Deal Won Schools')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('process_status', 'deal_won')) 
    //             ->badge(fn () => $this->getDemoWonSchoolsCount()), 

    //         'deal_lost' => Tab::make('Deal Lost Schools')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('process_status', 'deal_lost')) 
    //             ->badge(fn () => $this->getDemoLostSchoolsCount()), 
                
    //     ];
    // }

    // /**
    //  * Get the count of all schools.
    //  */
    // protected function getAllSchoolsCount(): int
    // {
    //     return SchoolResource::getEloquentQuery()->count();
    // }

    // /**
    //  * Get the count of schools with the "Demo Scheduled" process status.
    //  */
    // protected function getDemoWonSchoolsCount(): int
    // {
    //     return SchoolResource::getEloquentQuery()
    //         ->where('process_status', 'deal_won')
    //         ->count();
    // }

    // protected function getDemoLostSchoolsCount(): int
    // {
    //     return SchoolResource::getEloquentQuery()
    //         ->where('process_status', 'deal_lost')
    //         ->count();
    // }


    // protected function getDemoScheduledSchoolsCount(): int
    // {
    //     return SchoolResource::getEloquentQuery()
    //         ->where('process_status', 'Demo Scheduled')
    //         ->count();
    // }
}
