<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookShipmentResource\Pages;
use App\Filament\Resources\BookShipmentResource\RelationManagers;
use App\Filament\Resources\BookShipmentResource\RelationManagers\BookShipmentsRelationManager;
use App\Filament\Resources\BookShipmentResource\RelationManagers\SchoolBookRelationManager;
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
    protected static ?string $model = School::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    
    protected static ?string $navigationLabel = 'Book Shipment ';

    protected static ?string $pluralLabel = 'Book Shipment ';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'sales_operation' , 'company' , 'sales_operation_head' , 'head' , 'zonal_manager' , 'regional_manager' , 'bda' , 'bdm' ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('School Name')
                    ->sortable()
                    ->searchable(),
    
                // Total Books Count (Summing up all books assigned to the school)
                TextColumn::make('total_books')
                    ->label('Total Books')
                    ->getStateUsing(fn (School $record) => 
                        $record->schoolBook->sum('books_count') // Summing total books count
                    )
                    ->sortable(),

                    
    
                // Issued Books Count (Summing up issued books from SchoolBook model)
                TextColumn::make('issued_books')
                    ->label('Issued Books')
                    ->getStateUsing(fn (School $record) => 
                        $record->schoolBook->sum('issued_books_count') // Summing issued books
                    )
                    ->sortable(),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([])
            ->paginated([10, 25]);
    }
    
    public static function getRelations(): array
    {
        return [
            SchoolBookRelationManager::class,
            BookShipmentsRelationManager::class,
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
