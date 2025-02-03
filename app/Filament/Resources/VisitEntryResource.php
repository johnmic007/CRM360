<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolResource\RelationManagers\LeadStatusesRelationManager;
use App\Filament\Resources\VisitEntryResource\Pages;
use App\Filament\Resources\VisitEntryResource\RelationManagers\SchoolVisitRelationManager;
use App\Models\Block;
use App\Models\District;
use App\Models\SalesLeadManagement;
use App\Models\VisitEntry;
use App\Models\TrainerVisit;
use App\Models\SalesLeadStatus;
use App\Models\School;
use App\Models\State;
use Illuminate\Support\Facades\DB;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Hidden;

class VisitEntryResource extends Resource
{
    protected static ?string $model = VisitEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole([ 'zonal_manager', 'regional _manager', 'senior_manager', 'bdm' , 'bda']);
    }



    public static function getActions(): array
    {
        return [
            // Define the "Start" action here
            Action::make('start')
                ->label('Start')
                ->color('success')

        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Section for Starting Details



            Hidden::make('user_id')
                ->default(fn() => auth()->id()),



                Forms\Components\TextInput::make('start_time')
                ->label('Start Time')
                ->visible(fn ($record) => $record && $record->start_time) // Show only if start_time has a value
                ->default(fn ($record) => $record ? $record->start_time : null)
                ->disabled(), // Make the field read-only
    
            Forms\Components\TextInput::make('end_time')
                ->label('End Time')
                ->visible(fn ($record) => $record && $record->end_time) // Show only if end_time has a value
                ->default(fn ($record) => $record ? $record->end_time : null)
                ->disabled(), 
          
           
                

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start Time')
                    ->dateTime(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('End Time')
                    ->dateTime(),

                Tables\Columns\TextColumn::make('trainerVisit.starting_km')
                    ->label('Starting KM'),

                Tables\Columns\TextColumn::make('trainerVisit.ending_km')
                    ->label('Ending KM'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->paginated([10, 25,]);

    }



   

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitEntries::route('/'),
            'create' => Pages\CreateVisitEntry::route('/create'),
            'edit' => Pages\EditVisitEntry::route('/{record}/edit'),
        ];
    }
}
