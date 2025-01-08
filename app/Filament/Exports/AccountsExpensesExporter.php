<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AccountsExpensesExporter  extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')
                ->label('Name'),

            ExportColumn::make('roles')
                ->label('Roles')
                ->state(fn($record) => $record->roles->pluck('name')->join(', ')),

            ExportColumn::make('total_amount_received')
                ->label('Total Amount Received')
                ->state(fn($record) => $record->walletLogs()->where('type', 'credit')->sum('amount')),

            ExportColumn::make('total_amount_spent')
                ->label('Total Amount Spent')
                ->state(fn($record) => $record->walletLogs()->where('type', 'debit')->sum('amount')),

            ExportColumn::make('wallet_balance')
                ->label('Wallet Balance'),

            ExportColumn::make('last_expense')
                ->label('Last Expense')
                ->state(fn($record) => $record->trainerVisits()->latest('visit_date')->value('total_expense') ?? 'N/A'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $url = route('filament.actions.export', ['id' => $export->getKey()]);
        $body = 'Your export has completed, and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' were exported.';

        $body .= ' You can download your file <a href="' . $url . '">here</a>.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
