<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExtraExpenseCategoryResource\Pages;
use App\Models\ExtraExpenseCategory;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExtraExpenseCategoryResource extends Resource
{
    protected static ?string $model = ExtraExpenseCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationGroup = 'Utilities';
    protected static ?string $navigationLabel = 'Expense Categories';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'sales_head' , 'head' , 'sales_operation' , 'sales_operation_head']);
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole(['admin']);

    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Category Details')
                    ->description('Manage expense categories here.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Category Name')
                            ->placeholder('Enter category name')
                            ->required()
                            ->unique(ExtraExpenseCategory::class, 'name')
                            ->maxLength(255),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Category Name')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('primary')
                    ->tooltip('Expense category name'),

                    
                
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M, Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete Category?')
                    ->modalDescription('Are you sure you want to delete this category? This action cannot be undone.'),
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
            'index' => Pages\ListExtraExpenseCategories::route('/'),
            'create' => Pages\CreateExtraExpenseCategory::route('/create'),
            'edit' => Pages\EditExtraExpenseCategory::route('/{record}/edit'),
        ];
    }
}
