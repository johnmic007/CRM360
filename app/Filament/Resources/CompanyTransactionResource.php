<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyTransactionResource\Pages;
use App\Filament\Resources\CompanyTransactionResource\RelationManagers;
use App\Models\CompanyTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyTransactionResource extends Resource
{
    protected static ?string $model = CompanyTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['old' ]);
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
                //
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
            'index' => Pages\ListCompanyTransactions::route('/'),
            'create' => Pages\CreateCompanyTransaction::route('/create'),
            'edit' => Pages\EditCompanyTransaction::route('/{record}/edit'),
        ];
    }
}
