<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainerVisitResource\Pages;
use App\Models\TrainerVisit;
use App\Models\User;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;

class TrainerVisitResource extends Resource
{
    protected static ?string $model = TrainerVisit::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Travel Logs';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Hidden::make('user_id')
                    ->default(auth()->id())
                    ->required(),

                Hidden::make('company_id')
                    ->default(fn() => auth()->user()->company_id) 
                    ->required(),


                Select::make('user_id')
                    ->label('Name')
                    ->disabled()
                    ->relationship('user', 'name')
                    ->required(),

                Select::make('school_id')
                    ->label('School')
                    ->multiple()
                    ->preload()
                    ->relationship('school', 'name')
                    ->required(),

                DatePicker::make('visit_date')
                    ->label('Visit Date')
                    ->default(now())
                    ->required(),

                Select::make('travel_mode')
                    ->label('Travel Mode')
                    ->options([
                        'car' => 'Car',
                        'bike' => 'Bike',
                    ])
                    ->reactive()
                    ->required(),

                // Photo of the starting meter
                FileUpload::make('starting_meter_photo')
                    ->label('Starting Meter Photo')
                    ->image(),
                // ->required(),

                // Starting Kilometer Input
                TextInput::make('starting_km')
                    ->label('Starting Kilometer')
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $endingKm = $get('ending_km');
                        if ($endingKm !== null && $state !== null) {
                            $distance = max(0, $endingKm - $state);
                            $set('distance_traveled', $distance);

                            // Calculate travel expense
                            $travelMode = $get('travel_mode');
                            $rate = $travelMode === 'car'
                                ? Setting::getCarRate()
                                : Setting::getBikeRate();
                            $set('travel_expense', $rate * $distance);
                        }
                    }),


                // Photo of the ending meter
                FileUpload::make('ending_meter_photo')
                    ->label('Ending Meter Photo')
                    ->image(),

                // Ending Kilometer Input

                TextInput::make('ending_km')
                    ->label('Ending Kilometer')
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $startingKm = $get('starting_km');
                        if ($startingKm !== null && $state !== null) {
                            $distance = max(0, $state - $startingKm);
                            $set('distance_traveled', $distance);

                            // Calculate travel expense
                            $travelMode = $get('travel_mode');
                            $rate = $travelMode === 'car'
                                ? Setting::getCarRate()
                                : Setting::getBikeRate();
                            $set('travel_expense', $rate * $distance);
                        }
                    }),

                TextInput::make('distance_traveled')
                    ->label('Distance Traveled')
                    ->numeric()
                    ->readOnly()
                    ->default(0),

                TextInput::make('travel_expense')
                    ->label('Travel Expense')
                    ->numeric()
                    ->readOnly()
                    ->default(0),


                TextInput::make('food_expense')
                    ->label('Food Expense')
                    ->numeric()
                    ->readOnly()
                    ->default(Setting::getFoodExpenseRate()),

                FileUpload::make('travel_bill')
                    ->label('Travel Bill')
                    ->image()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Trainer'),
                // TextColumn::make('school.name')->label('School'),
                TextColumn::make('visit_date')->label('Visit Date')->date(),
                TextColumn::make('travel_mode')->label('Travel Mode'),
                TextColumn::make('starting_km')->label('Starting Kilometer'),
                TextColumn::make('ending_km')->label('Ending Kilometer'),
                TextColumn::make('distance_traveled')->label('Distance (km)'),
                TextColumn::make('travel_expense')->label('Travel Expense')->money('INR'),
                TextColumn::make('approved_by')->label('Approved By')
                    ->formatStateUsing(fn($state) => $state ? User::find($state)->name : 'Pending'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainerVisits::route('/'),
            'create' => Pages\CreateTrainerVisit::route('/create'),
            'edit' => Pages\EditTrainerVisit::route('/{record}/edit'),
        ];
    }
}
