<?php

namespace App\Filament\Resources\InvoResource\Pages;

use App\Filament\Resources\InvoResource;
use Filament\Resources\Pages\Page;
use App\Models\Invoice;

class ViewInvoice extends Page
{
    protected static string $resource = InvoResource::class;

    protected static string $view = 'filament.view-invoice.blade.php'; // Point to custom blade file

    public Invoice $record;

    public function mount($record): void
    {
        $this->record = Invoice::with(['school', 'company', 'items'])->findOrFail($record);
    }
}
