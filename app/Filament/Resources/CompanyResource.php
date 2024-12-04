<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'Utilities';



    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin']);
    }
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Company Name')
                ->required()
                ->unique(),
    
            Forms\Components\TextInput::make('email')
                ->label('Email')
                ->email()
                ->nullable(),
    
            Forms\Components\TextInput::make('phone')
                ->label('Phone')
                ->tel()
                ->nullable(),
    
            Forms\Components\Textarea::make('address')
                ->label('Address')
                ->nullable(),
        ]);
    }
    

    
    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')
                ->label('Company Name')
                ->sortable()
                ->searchable(),
    
            Tables\Columns\TextColumn::make('email')
                ->label('Email')
                ->sortable()
                ->searchable(),
    
            Tables\Columns\TextColumn::make('phone')
                ->label('Phone')
                ->sortable(),
    
            Tables\Columns\TextColumn::make('address')
                ->label('Address')
                ->wrap(),
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
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
