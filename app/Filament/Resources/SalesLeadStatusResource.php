<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesLeadStatusResource\Pages;
use App\Filament\Resources\SalesLeadStatusResource\Pages\SalesLeadStatusResource\ViewSalesLeadStatus;
use App\Filament\Resources\VisitEntryResource\RelationManagers\SchoolVisitRelationManager;
use App\Models\SalesLeadStatus;
use App\Models\VisitEntry;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;

class SalesLeadStatusResource extends Resource
{
    protected static ?string $model = VisitEntry::class;


    protected static ?string $navigationLabel = 'Visit Entry Logs';

    protected static ?string $pluralLabel = 'Visit Entry Logs';


    protected static ?string $navigationIcon = 'heroicon-o-bars-arrow-up';



    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasRole(['sales_operation_head', 'admin']);
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole(['admin']);
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

                Forms\Components\Select::make('travel_type')
                    ->options([
                        'own_vehicle' => 'Travel by Own Vehicle',
                        'with_colleague' => 'Travel with Colleague',
                    ])
                    ->required()
                    ->reactive()
                    ->label('Travel Type'),


                Forms\Components\FileUpload::make('starting_meter_photo')
                    ->label('Starting Meter Photo')
        ->optimize('webp')                ->disk('s3')
                    ->directory('CRM')
                    ->visible(fn(callable $get) => $get('travel_type') === 'own_vehicle'),

                Forms\Components\FileUpload::make('ending_meter_photo')
                    ->label('Ending Meter Photo')
        ->optimize('webp')                ->disk('s3')
                    ->directory('CRM')
                    ->visible(fn(callable $get) => $get('travel_type') === 'own_vehicle'),


                Forms\Components\FileUpload::make('travel_bill')
    ->optimize('webp')                ->disk('s3')
                ->directory('CRM')
                    ->visible(fn(callable $get) => $get('travel_type') === 'with_colleague')
                    ->label('Travel Bill'),
                Forms\Components\TextInput::make('travel_expense')
                    ->label('Travel Expense'),
                Forms\Components\TextInput::make('starting_km')
                    ->label('Starting KM')
                    ->visible(fn(callable $get) => $get('travel_type') === 'own_vehicle'),

                Forms\Components\TextInput::make('ending_km')
                    ->label('Ending KM')
                    ->visible(fn(callable $get) => $get('travel_type') === 'own_vehicle'),

                Forms\Components\Select::make('travel_mode')
                    ->visible(fn(callable $get) => $get('travel_type') === 'own_vehicle')
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
            TextColumn::make('user.name'),
            Tables\Columns\TextColumn::make('start_time')
                ->label('Start Time')
                ->dateTime(),

            Tables\Columns\TextColumn::make('end_time')
                ->label('End Time')
                ->dateTime(),

            Tables\Columns\BooleanColumn::make('completed')
                ->label('Visit Completed')
                ->getStateUsing(fn($record) => !is_null($record->end_time)),

            TextColumn::make('travel_type'),

            // TextColumn::make('total_expense'),






        ])
            ->filters([
                Filter::make('date_range')
                    ->label('Date Range')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('Start Date'),

                        DatePicker::make('end_date')
                            ->label('End Date')
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data) {
                        if (!empty($data['start_date']) && !empty($data['end_date'])) {
                            $query->whereBetween('created_at', [$data['start_date'], $data['end_date']]);
                        }
                    })
                    ->indicateUsing(function (array $data) {
                        if (!empty($data['start_date']) && !empty($data['end_date'])) {
                            return 'From ' . $data['start_date'] . ' to ' . $data['end_date'];
                        }
                        return null;
                    }),

                    SelectFilter::make('travel_type')
                    ->label('Travel Type')
                    ->options([
                        'own_vehicle' => 'Travel by Own Vehicle',
                        'with_colleague' => 'Travel with Colleague',
                    ])
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // Add View action
                Tables\Actions\EditAction::make(), // Add Edit action
                Tables\Actions\DeleteAction::make(), // Add Edit action

                
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
