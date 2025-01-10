<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClosureAmountResource\Pages;
use App\Filament\Resources\ClosureAmountResource\RelationManagers;
use App\Filament\Resources\ClosureAmountResource\RelationManagers\ClosureLogsRelationManager;
use App\Models\ClosureAmount;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClosureAmountResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Closure Amount';

    protected static ?string $pluralLabel = 'Closure Amount';

    protected static ?string $navigationGroup = 'Finance Management';



    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['accounts_head']);
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
                ->label('User Name')
                ->searchable(),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->getStateUsing(fn($record) => $record->roles->pluck('name')->join(', ')),


                    TextColumn::make('total_amount_given')
                    ->searchable(),

                    
                    TextColumn::make('total_amount_closed')
                ->searchable(),

                TextColumn::make('amount_to_close')
                ->searchable(),

            ])
            ->filters([
                //
            ])
            ->actions([
    
                Tables\Actions\Action::make('close_amount')
    ->label('Close Amount')
    ->icon('heroicon-o-currency-dollar')
    ->form([
        Forms\Components\TextInput::make('total_amount_given')
            ->label('Total Amount Given')
            ->disabled()
            ->default(fn ($record) => $record->total_amount_given),

        Forms\Components\TextInput::make('total_amount_closed')
            ->label('Total Amount Closed')
            ->disabled()
            ->default(fn ($record) => $record->total_amount_closed),

        Forms\Components\TextInput::make('amount_to_close')
            ->label('Amount to Close')
            ->disabled()
            ->default(fn ($record) => $record->amount_to_close),

        Forms\Components\TextInput::make('amount_to_be_closed')
            ->label('Amount to Close Now')
            ->required()
            ->numeric()
            ->minValue(1)
            ->maxValue(fn ($record) => $record->amount_to_close),
    ])
    ->action(function ($record, $data) {
        $amountToCloseNow = $data['amount_to_be_closed'];
        $user = auth()->user(); // Get the currently logged-in user

        // Update the closure amounts
        $record->update([
            'total_amount_closed' => $record->total_amount_closed + $amountToCloseNow,
            'amount_to_close' => $record->amount_to_close - $amountToCloseNow,
        ]);

        // Log the closure action
        \App\Models\ClosureAmountLog::create([
            'user_id' => $record->id, // Assuming the record represents the user
            'closed_by_id' => $user->id, // ID of the admin/user who performed the action
            'amount_closed' => $amountToCloseNow,
            'closed_at' => now(),
        ]);
    })
    ->requiresConfirmation()
    ->color('primary')

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
            ClosureLogsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClosureAmounts::route('/'),
            'create' => Pages\CreateClosureAmount::route('/create'),
            'edit' => Pages\EditClosureAmount::route('/{record}/edit'),
        ];
    }
}
