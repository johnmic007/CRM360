<?php

namespace App\Filament\Resources\BookShipmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\CreateAction;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

use App\Models\Book;
use App\Models\User;

class BookShipmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookShipments';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)
                ->schema([
                    Select::make('mode_of_transport')
                        ->label('Mode of Transport')
                        ->options([
                            'own' => 'Own Delivery',
                            'courier' => 'Courier',
                        ])
                        ->required()
                        ->reactive()
                        ->helperText('Choose the transport mode.'),

                    Select::make('closed_by')
                        ->label('Delivered By')
                        ->options(fn () => User::whereHas('roles', fn ($query) => 
                            $query->whereIn('name', ['BDA', 'BDM'])
                        )->pluck('name', 'id'))
                        ->searchable()
                        ->placeholder('Person delivering the shipment')
                        ->visible(fn ($get) => $get('mode_of_transport') === 'own'),

                    TextInput::make('tracking_number')
                        ->label('Tracking Number')
                        ->placeholder('Courier tracking number')
                        ->visible(fn ($get) => $get('mode_of_transport') === 'courier'),

                    FileUpload::make('bills_and_gatepass')
                        ->label('Bills / Gate Pass')
                        ->directory('shipment_documents')
                        ->helperText('Upload related documents.')
                        ->nullable(),

                    Select::make('status')
                        ->label('Shipment Status')
                        ->options([
                            'initiated' => 'Initiated',
                            'delivery_initiated' => 'Delivery Initiated',
                            'on_the_way' => 'On the Way',
                            'delivered' => 'Delivered',
                        ])
                        ->required()
                        ->helperText('Track the progress of the shipment.'),

                    Textarea::make('remarks')
                        ->label('Remarks')
                        ->placeholder('Additional comments or notes.')
                        ->rows(2),
                ]),

            Forms\Components\Section::make('Books to Ship')
                ->schema([
                    Repeater::make('details')
                        ->relationship('details')
                        ->schema([
                            Select::make('book_id')
                                ->label('Book')
                                ->options(Book::pluck('title', 'id'))
                                ->searchable()
                                ->required(),

                            TextInput::make('quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->minValue(1)
                                ->required(),
                        ])
                        ->columns(2)
                        ->minItems(1)
                        ->grid(['default' => 2])
                        ->defaultItems(1)
                        ->createItemButtonLabel('Add Another Book'),
                ])
                ->collapsible()
                ->description('Add books to the shipment and specify their quantities.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
        ->columns([
            // TextColumn::make('id')
            //     ->label('ShipmID')
            //     ->sortable(),

            TextColumn::make('school.name')
                ->label('School')
                ->sortable()
                ->searchable(),

            TextColumn::make('mode_of_transport')
                ->label('Transport Mode')
                ->sortable(),

            // TextColumn::make('tracking_number')
            //     ->label('Tracking Number')
            //     ->placeholder('--')
            //     ->sortable()
            //     ->visible(fn ($record) => $record->mode_of_transport === 'courier'),

            TextColumn::make('closedBy.name')
                ->label('Delivered By')
                ->sortable()
                ->placeholder('--'),
                // ->visible(fn ($record) => $record->mode_of_transport === 'own'),

                TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->sortable()
                ->color(fn ($state) => match ($state) {
                    'delivered' => 'success', // Green
                    'on_the_way' => 'warning', // Yellow
                    'delivery_initiated' => 'info', // Blue
                    'initiated' => 'gray', // Gray
                    default => 'gray', // Default color
                }),            
            // TextColumn::make('remarks')
            //     ->label('Remarks')
            //     ->limit(50)
            //     ->tooltip(),

            // ImageColumn::make('bills_and_gatepass')
            //     ->label('Bill / Gate Pass')
            //     ->square()
            //     ->size(40),

            TextColumn::make('created_at')
                ->label('Date Created')
                ->date('Y-m-d') // Show only date
                ->sortable(),
        ])
        ->filters([])
        ->actions([
            EditAction::make()
                ->visible(fn ($record) => 
                    auth()->user()->hasRole('admin') || 
                    (auth()->user()->hasRole(['sales_operation', 'sales_operation_head']) && $record->status !== 'delivered')
                ),
        ])
        

        ->headerActions([
            CreateAction::make()
                ->visible(fn () => auth()->user()->hasRole(['admin', 'sales_operation', 'sales_operation_head'])),
        ])
        ->bulkActions([])
        ->paginated([10, 25]);
    }
}
