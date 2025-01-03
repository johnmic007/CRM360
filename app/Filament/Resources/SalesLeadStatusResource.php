<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesLeadStatusResource\Pages;
use App\Filament\Resources\SalesLeadStatusResource\Pages\SalesLeadStatusResource\ViewSalesLeadStatus;
use App\Filament\Resources\VisitEntryResource\RelationManagers\SchoolVisitRelationManager;
use App\Models\SalesLeadStatus;
use App\Models\VisitEntry;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class SalesLeadStatusResource extends Resource
{
    protected static ?string $model = VisitEntry::class;


     protected static ?string $navigationLabel = 'Visit Entry Logs';

    protected static ?string $pluralLabel = 'Visit Entry Logs';


    protected static ?string $navigationIcon = 'heroicon-o-bars-arrow-up';


    
    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasRole([  'sales_operation_head',]);

        
    }





    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('start_time')
                    ->required()
                    ->label('Start Time'),
                Forms\Components\TextInput::make('end_time')
                    ->required()
                    ->label('End Time'),
                    Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->disabled()
                    ->relationship('user', 'name') // Specify the relationship and the display column (e.g., `name`)
                    ->required(),
              
                Forms\Components\FileUpload::make('starting_meter_photo')
                    ->label('Starting Meter Photo'),
                Forms\Components\FileUpload::make('ending_meter_photo')
                    ->label('Ending Meter Photo'),
                Forms\Components\Select::make('travel_type')
                ->options([
                    'own_vehicle' => 'Travel by Own Vehicle',
                    'with_colleague' => 'Travel with Colleague',
                ])
                    ->required()
                    ->label('Travel Type'),
                Forms\Components\TextInput::make('travel_bill')
                    ->label('Travel Bill'),
                Forms\Components\TextInput::make('travel_expense')
                    ->label('Travel Expense'),
                Forms\Components\TextInput::make('starting_km')
                    ->label('Starting KM'),
                Forms\Components\TextInput::make('ending_km')
                    ->label('Ending KM'),
                Forms\Components\Select::make('travel_mode')
                ->options([
                    'car' => 'Car',
                    'bike' => 'Bike',
                ])
                    ->label('Travel Mode'),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id')->label('ID')->sortable(),
            Tables\Columns\TextColumn::make('travel_type'),

            Tables\Columns\TextColumn::make('travel_expense'),

            

            Tables\Columns\TextColumn::make('user.name'),

          
            
        ])
        ->actions([
            Tables\Actions\ViewAction::make(), // Add View action
            Tables\Actions\EditAction::make(), // Add Edit action
        ]);
    }

    public static function getRelations(): array
    {
        return [
            SchoolVisitRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesLeadStatuses::route('/'),
            'create' => Pages\CreateSalesLeadStatus::route('/create'),
            'edit' => Pages\EditSalesLeadStatus::route('/{record}/edit'),
            'view' => ViewSalesLeadStatus::route('/{record}'),

        ];
    }
}
