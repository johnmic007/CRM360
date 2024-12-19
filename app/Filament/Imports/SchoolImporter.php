<?php

namespace App\Filament\Imports;

use App\Models\School;
use App\Models\District;
use App\Models\Block;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Log;

class SchoolImporter extends Importer
{
    protected static ?string $model = School::class;

    /**
     * Define the columns that can be imported.
     */
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('district')
                ->label('District')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('block_name')
                ->label('Block Name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('name')
                ->label('School Name')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('address')
                ->label('School Address')
                ->rules(['nullable', 'string', 'max:255']),

            ImportColumn::make('pincode')
                ->label('Pincode')
                ->rules(['nullable', 'string', 'max:6']),
        ];
    }

    /**
     * Hook runs before the CSV data for a row is validated.
     */
    protected function beforeValidate(): void
    {
        Log::info('Starting validation for this row of the import.', ['row' => $this->originalData]);
    }

    /**
     * Hook runs before a record is saved to the database.
     */
    protected function beforeSave(): void
    {
        $row = $this->data;

        Log::info('Processing row before save:', $row);

        // Validate district and block_name
        if (empty($row['district']) || empty($row['block_name'])) {
            Log::error('Missing district or block name.', ['row' => $row]);
            throw new \Exception('District and Block Name are required.');
        }

        // Create or find the District
        $district = District::firstOrCreate(['name' => $row['district']]);
        if (!$district->id) {
            Log::error('Failed to create or find district.', ['name' => $row['district']]);
            throw new \Exception('Failed to create or find district.');
        }

        // Create or find the Block
        $block = Block::firstOrCreate([
            'name' => $row['block_name'],
            'district_id' => $district->id,
        ]);
        if (!$block->id) {
            Log::error('Failed to create or find block.', ['name' => $row['block_name'], 'district_id' => $district->id]);
            throw new \Exception('Failed to create or find block.');
        }

        // Set the district_id and block_id on the record before it saves
        $this->record->district_id = $district->id;
        $this->record->block_id = $block->id;
    }

    /**
     * Notification displayed when the import starts.
     */
    public static function getStartedNotificationBody(Import $import): string
    {
        return 'The import has started and ' . number_format($import->total_rows) . ' rows will be processed.';
    }

    /**
     * Notification displayed when the import completes.
     */
    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import completed successfully. ' . number_format($import->successful_rows) . ' rows were imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' rows failed to import. Please review the failed rows file.';
        }

        return $body;
    }

    /**
     * Notification displayed when the import fails entirely.
     */
    public static function getFailedNotificationBody(Import $import): string
    {
        return 'The import process encountered errors. Please review the failed rows and try again.';
    }
}
