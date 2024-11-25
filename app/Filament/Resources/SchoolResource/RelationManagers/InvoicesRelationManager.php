<?php

namespace App\Filament\Resources\SchoolResource\RelationManagers;

use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;

use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoicesRelationManager  extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $recordTitleAttribute = 'invoice_number';


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
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
                    ->money('USD') // Adjust the currency as needed
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
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
                Action::make('create')
                    ->label('Create Invoice')
                    ->icon('heroicon-o-plus') // Optional: Add an icon
                    ->url(fn() => "/admin/invos/create?school_id=" . $this->ownerRecord->id) // Pass the school ID
                // ->color('success'), // Optional: Add color styling
                // Optional: Add color styling
            ])
            ->actions([
                
                Action::make('edit')
                    ->label('Edit')
                    ->url(fn($record) => "/admin/invos/{$record->id}/edit"),
                    Tables\Actions\Action::make('Download')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Invoice $record) => route('invoice.download', $record->id))
                    ->openUrlInNewTab(),
                    // Tables\Actions\Action::make('View')
                    //     ->label('View')
                    //     ->url(fn (Invoice $record) => InvoResource::getUrl('view', ['record' => $record->id]))
                    //     ->icon('heroicon-o-eye')
                    //     ->openUrlInNewTab(),
    
                    Tables\Actions\Action::make('Pay')
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
                                ->rules([
                                    'numeric',
                                    'min:0',
                                ])
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
    }
}
