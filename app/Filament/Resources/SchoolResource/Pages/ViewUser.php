<?php

namespace App\Filament\Resources\SchoolResource\Pages;

use App\Filament\Resources\SchoolResource;
use App\Models\Invoice;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\TextInput;


class ViewUser extends ViewRecord
{

    protected static string $resource = SchoolResource::class;

    protected function getFormSchema(): array
    {
        return [
            Tabs::make('School Details')
                ->tabs([
                    Tab::make('Overview')
                        ->schema(parent::getFormSchema()), // Default fields

                    Tab::make('Invoices')
                        ->schema([
                            $this->invoicesTable(),
                        ]),
                ]),
        ];
    }

    protected function invoicesTable()
    {
        return Tables\Table::make()
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('USD') // Adjust currency
                    ->sortable(),

                TextColumn::make('paid')
                    ->label('Paid')
                    ->money('USD') // Adjust currency
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->color(function ($state) {
                        return match ($state) {
                            'draft' => 'gray',
                            'sent' => 'yellow',
                            'paid' => 'green',
                            default => 'gray',
                        };
                    })
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                // You can add filters here
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Create Invoice')
                    ->icon('heroicon-o-plus')
                    ->url(fn () => "/admin/invos/create?school_id=" . $this->ownerRecord->id),
            ])
            ->actions([
                Action::make('edit')
                    ->label('Edit')
                    ->url(fn ($record) => "/admin/invos/{$record->id}/edit"),

                Action::make('Download PDF')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Invoice $record) => route('invoice.download', $record->id))
                    ->openUrlInNewTab(),

                Action::make('Pay')
                    ->label('Pay')
                    ->icon('heroicon-o-credit-card')
                    ->color('primary')
                    ->modalHeading('Pay Invoice')
                    ->form([
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->disabled()
                            ->default(fn (Invoice $record) => $record->total_amount), // Display total amount

                        TextInput::make('paid')
                            ->label('Amount Paid')
                            ->numeric()
                            ->disabled()
                            ->default(fn (Invoice $record) => $record->paid), // Display paid amount

                        TextInput::make('amount')
                            ->label('Amount to Pay')
                            ->numeric()
                            ->required()
                            ->rules(['numeric', 'min:0'])
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $get, $state) {
                                if ($state > $get('total_amount') - $get('paid')) {
                                    $set('amount', $get('total_amount') - $get('paid')); // Prevent overpayment
                                }
                            }),
                    ])
                    ->action(function (array $data, Invoice $record) {
                        $remaining = $record->total_amount - $record->paid;
                        $payment = min($data['amount'], $remaining);

                        // Update the invoice with the new paid amount and status
                        $record->update([
                            'paid' => $record->paid + $payment,
                            'status' => $record->paid + $payment >= $record->total_amount ? 'paid' : $record->status,
                        ]);

                        // Create a log entry for the payment
                        $record->logs()->create([
                            'type' => 'payment',
                            'description' => 'Paid amount: ' . $payment,
                        ]);
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Invoice $record) => $record->paid < $record->total_amount),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }}
