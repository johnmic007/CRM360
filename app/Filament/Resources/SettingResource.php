<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Filament\Resources\SettingResource\RelationManagers;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationGroup = 'Utilities';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'sales_operation','sales_operation_head' , ]);
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('car_rate')
                ->label('Car expense')
                ->numeric()
                ->required(),
            TextInput::make('bike_rate')
                ->label('Bike expense')
                ->numeric()
                ->required(),
            TextInput::make('food_expense_rate')
                ->label('Food expense')
                ->numeric()
                ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('car_rate')
                ->label('Car expense'),
                TextColumn::make('bike_rate')
                ->label('Bike expense'),
                TextColumn::make('food_expense_rate')
                ->label('Food Expense ')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
