<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountsClosingResource\Pages;
use App\Filament\Resources\AccountsClosingResource\RelationManagers;
use App\Filament\Resources\WalletLogResource\RelationManagers\AssociatedDebitsRelationManager;
use App\Models\AccountsClosing;
use App\Models\User;
use App\Models\WalletLog;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccountsClosingResource extends Resource
{
    protected static ?string $model = WalletLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $label = 'Accounts Closing'; // Singular form

    protected static ?string $pluralLabel = 'Accounts Closing';

    protected static ?string $navigationGroup = 'Finance Management';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'accounts_head' , 'company' ]);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasRole(['admin', 'accounts_head' ]);

    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('amount')->label('Amount')->disabled(),
                TextInput::make('balance')->label('Balance')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('User'),
                TextColumn::make('amount')->label('Amount')->money('INR'),
                TextColumn::make('balance')->label('Balance')->money('INR'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge() // Use badge styling
                    ->colors([
                        'success' => 'credit',
                        'danger' => 'debit',
                    ]),
                TextColumn::make('description')->label('Description'),
                TextColumn::make('created_at')->label('Date')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                ->label('User')
                ->options(User::pluck('name', 'id')->toArray())
                ->query(function (Builder $query, array $data) {
                    if (!empty($data['value'])) {
                        $query->where('user_id', $data['value']);
                    }
                }),
            ], layout: FiltersLayout::AboveContent  )
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
            
    }

    public static function getRelations(): array
    {
        return [
            AssociatedDebitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountsClosings::route('/'),
            'create' => Pages\CreateAccountsClosing::route('/create'),
            'edit' => Pages\EditAccountsClosing::route('/{record}/edit'),
        ];
    }
}
