<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CreateExpnessResource\Pages;
use App\Filament\Resources\CreateExpnessResource\RelationManagers;
use App\Filament\Resources\CreateExpnessResource\RelationManagers\CreateLeadStatusesRelationManager;
use App\Filament\Resources\VisitEntryResource\RelationManagers\SchoolVisitRelationManager;
use App\Models\CreateExpness;
use App\Models\Setting;
use App\Models\TrainerVisit;
use App\Models\VisitEntry;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CreateExpnessResource extends Resource
{
    protected static ?string $model = VisitEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    protected static ?string $navigationLabel = 'Add New Visit ';

    protected static ?string $pluralLabel = 'Add New Visit ';

    protected static ?string $navigationGroup = 'New Entry';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'sales_operation_head', 'accounts_head']);
    }



    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([

                Hidden::make('created_by')
                    ->default(auth()->id())
                    ->required(),

                Hidden::make('company_id')
                    ->default(fn() => auth()->user()->company_id)
                    ->required(),

                Select::make('user_id')
                    ->label('Name')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('travel_type')
                    ->label('Travel Type')
                    ->options([
                        'own_vehicle' => 'Travel by Own Vehicle',
                        'with_colleague' => 'Travel with Colleague',
                    ])
                    ->reactive()
                    ->required(),

                // Fields for "own_vehicle" travel type
                TimePicker::make('start_time')
                    ->label('Start Time')
                    ->required(),

                TimePicker::make('end_time')
                    ->label('End Time')
                    ->required(),

                TextInput::make('starting_km')
                    ->numeric()
                    ->required()
                    ->hidden(fn($get) => $get('travel_type') !== 'own_vehicle'),

                TextInput::make('ending_km')
                    ->numeric()
                    ->required()
                    ->hidden(fn($get) => $get('travel_type') !== 'own_vehicle'),

                // TextInput::make('travel_expense')
                //     ->label('Travel Expense')
                //     ->numeric()
                //     ->required()
                //     ->hidden(fn($get) => $get('travel_type') !== 'own_vehicle'),

                FileUpload::make('starting_meter_photo')
                    ->label('Starting Meter Photo')
                    ->required()
                    ->disk('s3')
                    ->directory('CRM')
                    ->hidden(fn($get) => $get('travel_type') !== 'own_vehicle'),

                FileUpload::make('ending_meter_photo')
                    ->label('Ending Meter Photo')
                    ->required()
                    ->disk('s3')
                    ->directory('CRM')
                    ->hidden(fn($get) => $get('travel_type') !== 'own_vehicle'),

                Select::make('travel_mode')
                    ->label('Travel Mode')
                    ->options([
                        'car' => 'Car',
                        'bike' => 'Bike',
                    ])
                    ->required()
                    ->hidden(fn($get) => $get('travel_type') !== 'own_vehicle'),

                // Fields for "public_transport" travel type
                FileUpload::make('travel_bill')
                    ->label('Travel Bill (Bus/Train)')
                    ->disk('s3')
                    ->directory('CRM')
                    // ->disk('s3')
                    // ->public()
                    // ->webp()
                    // ->directory('CRM')
                    ->required()
                    ->hidden(fn($get) => $get('travel_type') !== 'with_colleague'),



                TextInput::make('travel_expense')
                    ->label('Travel Expense')
                    ->numeric()
                    ->required()
                    ->hidden(fn($get) => $get('travel_type') !== 'with_colleague'),

                // Common fields
                DatePicker::make('visit_date')
                    ->label('Visit Date')
                    ->required(),


            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name'),

                TextColumn::make('travel_type'),

                TextColumn::make('travel_expense'),
            ]);

        // ->bulkActions([
        //     Tables\Actions\BulkAction::make('downloadPdf')
        //         ->label('Download as PDF')
        //         ->action(fn($records) => self::downloadPdf($records)),
        // ]);
    }

    public static function getRelations(): array
    {
        return [
            CreateLeadStatusesRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCreateExpnesses::route('/'),
            'create' => Pages\CreateCreateExpness::route('/create'),
            'edit' => Pages\EditCreateExpness::route('/{record}/edit'),
        ];
    }
}
