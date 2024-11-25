<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceLogResource\RelationManagers\InvoiceLogRelationManager;
use App\Filament\Resources\InvoiceLogResource\RelationManagers\NameRelationManager;
use App\Filament\Resources\InvoResource\Pages;
use App\Filament\Resources\InvoResource\RelationManagers;
use App\Filament\Resources\PaymentResource\RelationManagers\PaymentRelationManager;
use App\Models\Invo;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\User;
use App\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\HasManyRepeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Invoice Details Section

            TextInput::make('invoice_number')
                ->label('Invoice Number')
                ->required()
                ->unique(),

            Section::make('Invoice Details')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('school_id')
                                ->label('School')
                                ->relationship('school', 'name')
                                ->required()
                                ->default(fn() => request()->query('school_id')), // Set default value from query parameter


                            Select::make('company_id')
                                ->label('Company')
                                ->relationship('company', 'name')
                                ->required()
                                ->hidden()
                                ->default(fn() => auth()->user()->company_id) // Set default from logged-in user's company_id
                                ->disabled(), // Make the field non-editable



                            DatePicker::make('issue_date')
                                ->label('Issue Date')
                                ->required(),

                            DatePicker::make('due_date')
                                ->label('Due Date')
                                ->nullable(),

                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'draft' => 'Draft',
                                    'sent' => 'Sent',
                                    'paid' => 'Paid',
                                ])
                                ->default('draft'),
                        ]),
                ])
                ->collapsible()
                ->collapsed(false),

            // Invoice Items Section
            Section::make('Invoice Items')
                ->schema([
                    HasManyRepeater::make('items')
                        ->relationship('items')
                        ->schema([
                            TextInput::make('item_name')
                                ->label('Item Name')
                                ->required(),

                            TextInput::make('description')
                                ->label('Description')
                                ->nullable(),

                            Grid::make(3)
                                ->schema([
                                    TextInput::make('quantity')
                                        ->label('Quantity')
                                        ->numeric()
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function (callable $set, $get, $state) {
                                            $set('total', $state * ($get('price') ?? 0));
                                        }),

                                    TextInput::make('price')
                                        ->label('Price')
                                        ->numeric()
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function (callable $set, $get, $state) {
                                            $set('total', ($get('quantity') ?? 0) * $state);
                                        }),

                                    TextInput::make('total')
                                        ->label('Total')
                                        ->numeric()
                                        ->disabled()
                                        ->default(0),
                                ]),
                        ])
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, $get) {
                            // Recalculate total_amount whenever items change
                            $items = $get('items') ?? [];
                            $totalAmount = collect($items)->sum(fn($item) => ($item['total'] ?? 0));
                            $set('total_amount', $totalAmount);
                        })
                        ->createItemButtonLabel('Add Item'),
                ])
                ->collapsible()
                ->collapsed(false),

            // Total Amount Section
            Section::make('Summary')
                ->schema([
                    TextInput::make('total_amount')
                        ->label('Total Amount')
                        ->numeric()
                        ->disabled()
                        ->default(0)
                        ->extraAttributes(['class' => 'text-xl font-bold']),
                ])
                ->collapsible()
                ->collapsed(false),


            Section::make('Deal Closure Details')
                ->schema([
                    Select::make('closed_by_id')
                        ->label('Closed By')
                        ->options(function () {
                            $currentUser = auth()->user();

                            // Get subordinates using the scope defined in the User model
                            $subordinates = User::query()->viewableBy($currentUser)->pluck('name', 'id');

                            return $subordinates;
                        })
                        ->required()
                        ->searchable(),
                ])
                ->collapsible()
                ->collapsed(false),

            // Payment Section
            // Section::make('Payment')
            //     ->schema([
            //         TextInput::make('total_amount')
            //             ->label('Total Amount')
            //             ->numeric()
            //             ->disabled()
            //             ->default(0),

            //         TextInput::make('paid')
            //             ->label('Amount Paid')
            //             ->numeric()
            //             ->disabled()
            //             ->default(0),

            //         TextInput::make('amount')
            //             ->label('Amount to Pay')
            //             ->numeric()
            //             ->minValue(0)
            //             ->maxValue(fn ($get) => $get('total_amount') - $get('paid'))
            //             ->required(fn ($get) => ($get('total_amount') - $get('paid')) > 0)
            //             ->visible(fn ($get) => ($get('total_amount') - $get('paid')) > 0)
            //             ->reactive(),
            //     ])
            //     ->collapsible()
            //     ->collapsed(false),


            Textarea::make('deal_closure_description')
                ->label('Description')
                ->placeholder('Provide additional details about the deal closure')
                ->required()
                ->maxLength(255),


        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->query(function (Builder $query) {
            $user = auth()->user();
        
            if (!$user || !$user->company_id) {
                return $query->whereRaw('1 = 0'); // Return no records if the user or company_id is missing
            }
        
            return $query->where('company_id', $user->company_id);
        })
        
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('school.name')
                    ->label('School')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('company.name')
                    ->label('Company')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('USD') // Adjust the currency as needed
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
                    ->badge() // Adds the badge styling to the column
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('school_id')
                    ->label('School')
                    ->relationship('school', 'name'),

                SelectFilter::make('company_id')
                    ->label('Company')
                    ->relationship('company', 'name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'paid' => 'Paid',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Download')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn(Invoice $record) => route('invoice.download', $record->id))
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
                            ->default(fn(Invoice $record) => $record->total_amount), // Display total amount
                        TextInput::make('paid')
                            ->label('Amount Paid')
                            ->numeric()
                            ->disabled()
                            ->default(fn(Invoice $record) => $record->paid), // Display paid amount
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
                    ->visible(fn(Invoice $record) => $record->paid < $record->total_amount), // Show button only if unpaid amount exists
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            InvoiceLogRelationManager::class,
            PaymentRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvos::route('/'),
            'create' => Pages\CreateInvo::route('/create'),
            'edit' => Pages\EditInvo::route('/{record}/edit'),
            'view' => Pages\ViewInvoice::route('/{record}/view'),
        ];
    }
}
