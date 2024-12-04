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

                Select::make('user_id')
                    ->label('Name')
                    ->disabled()
                    ->relationship('user', 'name')
                    ->required(),

                Select::make('school_id')
                    ->label('School')
                    ->relationship('school', 'name')
                    ->required(),
                DatePicker::make('visit_date')->required(),

                Select::make('travel_mode')
                    ->options([
                        'car' => 'Car',
                        'bike' => 'Bike',
                    ])
                    ->reactive(),

                TextInput::make('distance_traveled')
                    ->numeric()
                    ->reactive()
                    ->required()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        $travelMode = $get('travel_mode');
                        $distance = $state;

                        if ($travelMode && $distance) {
                            $rate = $travelMode === 'car'
                                ? Setting::getCarRate()
                                : Setting::getBikeRate();
                            $set('travel_expense', $rate * $distance);
                        } else {
                            $set('travel_expense', 0);
                        }
                    }),

                TextInput::make('travel_expense')
                    ->label('Travel Expense')
                    ->numeric()
                    ->disabled()
                    ->default(0),

                TextInput::make('food_expense')
                    ->label('Food Expense')
                    ->numeric()
                    ->disabled()
                    ->default(Setting::getFoodExpenseRate()),

                FileUpload::make('gps_photo')->image()->nullable(),

                FileUpload::make('travel_bill')->image()->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Trainer'),
                TextColumn::make('school.name')->label('School'),
                TextColumn::make('visit_date')->date(),
                TextColumn::make('travel_mode')->label('Travel Mode'),
                TextColumn::make('distance_traveled')->label('Distance (km)'),
                TextColumn::make('travel_expense')->label('Travel Expense')->money('INR'),
                TextColumn::make('approved_by')->label('Approved By')
                    ->formatStateUsing(fn ($state) => $state ? User::find($state)->name : 'Pending'),
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
