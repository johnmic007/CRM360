<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookShipmentResource\Pages;
use App\Filament\Resources\BookShipmentResource\RelationManagers;
use App\Models\Block;
use App\Models\Book;
use App\Models\BookShipment;
use App\Models\District;
use App\Models\School;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookShipmentResource extends Resource
{
    protected static ?string $model = BookShipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'sales_operation', 'sales_operation_head' ,]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make()
                ->schema([
                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\Select::make('state_id')
                                ->label('State')
                                ->options(\App\Models\State::pluck('name', 'id')->toArray()) // Fetch states using Eloquent
                                ->reactive()
                                ->required()
                                ->afterStateUpdated(fn(callable $set) => $set('district_id', null)), // Reset district when state changes

                            Forms\Components\Select::make('district_id')
                                ->label('District')
                                ->options(function (callable $get) {
                                    $stateId = $get('state_id');
                                    if (!$stateId) {
                                        return [];
                                    }
                                    // Fetch districts for the chosen state
                                    return \App\Models\District::where('state_id', $stateId)->pluck('name', 'id')->toArray();
                                })
                                ->reactive()
                                ->required()
                                ->afterStateUpdated(fn(callable $set) => $set('block_id', null)),

                            Forms\Components\Select::make('block_id')
                                ->label('Block')
                                ->options(function (callable $get) {
                                    $districtId = $get('district_id');
                                    if (!$districtId) {
                                        return [];
                                    }
                                    return Block::where('district_id', $districtId)->pluck('name', 'id')->toArray(); // Fetch blocks using Eloquent
                                })
                                ->reactive()
                                ->required(),

                            Forms\Components\Select::make('school_id')
                                ->label('School')
                               
                                ->options(function (callable $get) {
                                    $blockId = $get('block_id');
                                    if (!$blockId) {
                                        return [];
                                    }
                                    return School::where('block_id', $blockId)->pluck('name', 'id');
                                })

                                ->reactive()
                                ->required()
                                ->helperText('Select the school to which books will be shipped.'),

                            Select::make('mode_of_transport')
                                ->label('Mode of Transport')
                                ->options([
                                    'own' => 'Own Delivery',
                                    'courier' => 'Courier',
                                ])
                                ->required()
                                ->reactive()
                                ->helperText('Choose the transport mode.'),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Select::make('closed_by')
                                ->label('Closed By')
                                ->options(function () {
                                    $currentUser = auth()->user();

                                    // Get subordinates with specific roles (BDA and BDM) and the same company_id
                                    $subordinates = User::query()
                                        ->viewableBy($currentUser) // Assuming this scope limits to viewable users
                                        ->where('company_id', $currentUser->company_id) // Filter by the same company_id
                                        ->whereHas('roles', function ($query) {
                                            $query->whereIn('name', ['BDA', 'BDM']); // Filter roles to BDA and BDM
                                        })
                                        ->pluck('name', 'id');

                                    return $subordinates;
                                })
                                ->required()
                                ->searchable()->label('Delivered By')
                                ->placeholder('Person delivering the shipment')
                                ->visible(fn($get) => $get('mode_of_transport') === 'own'),

                            TextInput::make('tracking_number')
                                ->label('Tracking Number')
                                ->placeholder('Courier tracking number')
                                ->visible(fn($get) => $get('mode_of_transport') === 'courier'),
                        ]),

                    Forms\Components\FileUpload::make('bills_and_gatepass')
                        ->label('Bills / Gate Pass')
                      
                        ->directory('shipment_documents')
                        ->helperText('Upload related documents.')
                        ->nullable(),

                    Forms\Components\Grid::make(2)
                        ->schema([
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

                            Forms\Components\Textarea::make('remarks')
                                ->label('Remarks')
                                ->placeholder('Additional comments or notes.')
                                ->rows(2),
                        ]),
                ])
                ->columns(1),

            Forms\Components\Section::make('Books to Ship')
                ->schema([
                    Repeater::make('details')
                        ->relationship('details') // Refers to the books relationship in the model

                        ->schema([
                            Select::make('book_id')
                                ->label('Book')
                                ->options(
                                    \App\Models\Book::whereNotNull('title')->pluck('title', 'id')->toArray()
                                )
                                ->searchable()
                                ->required()
                                ->placeholder('Select a book'),

                            TextInput::make('quantity')
                                ->label('Quantity')
                                ->numeric()
                                ->minValue(1)
                                ->required(),
                        ])
                        ->columns(2)
                        ->minItems(1)
                        ->grid([
                            'default' => 2, // Ensures two repeater items appear per row
                        ])
                        ->defaultItems(1)
                        ->createItemButtonLabel('Add Another Book'),
                ])
                ->collapsible()
                ->description('Add books to the shipment and specify their quantities.'),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Shipment ID')
                    ->sortable(),

                TextColumn::make('school.name')
                    ->label('School')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookShipments::route('/'),
            'create' => Pages\CreateBookShipment::route('/create'),
            'edit' => Pages\EditBookShipment::route('/{record}/edit'),
        ];
    }
}
