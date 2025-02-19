<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\WalletLog;
use Filament\Resources\Resource;
use App\Models\InternalWalletTransfer;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\InternalWalletTransferResource\Pages;

class InternalWalletTransferResource extends Resource
{
    protected static ?string $model = InternalWalletTransfer::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Finance';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
        ->schema([

    Forms\Components\Select::make('from_user_id')
        ->label('From User')
        ->options(User::whereDoesntHave('roles', function ($query) {
            $query->whereIn('name', ['admin', 'accounts', 'accounts_head']);
        })->pluck('name', 'id'))
        ->required()
        ->reactive()
        ->afterStateUpdated(fn ($state, callable $set) =>
            $set('from_balance', WalletLog::where('user_id', $state)
                ->orderBy('balance', 'desc')
                ->value('balance') ?? 0)
        )
        ->visible(fn () => auth()->user()->hasRole('sales_operation_head')),

    Forms\Components\TextInput::make('from_balance')
        ->label('Available Balance')
        ->extraAttributes(['class' => 'update-field']), // Ensures UI updates

    Forms\Components\Select::make('to_user_id')
                    ->label('To User')
                    ->options(User::whereDoesntHave('roles', function ($query) {
                        $query->whereIn('name', ['admin', 'accounts', 'accounts_head']);
                    })->pluck('name', 'id'))
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) =>
            $set('to_balance', WalletLog::where('user_id', $state)
                ->orderBy('balance', 'desc')
                ->value('balance') ?? 0)
        ),

                Forms\Components\TextInput::make('to_balance')
                    ->label('Available Balance')
                    ->extraAttributes(['class' => 'update-field']), // Ensures UI updates,

                Forms\Components\TextInput::make('amount')
                    ->label('Transferable Amount')
                    ->numeric()
                    ->required()
                    ->live() // Ensures real-time updates
                    ->maxValue(fn ($get) => (float) $get('from_balance')) // Strictly limits value
                    ->rules([
                        fn ($get) => 'lte:' . ((float) $get('from_balance') ?: 0)
                    ])
                    ->hint(fn ($get) => 'Must be less than or equal to ₹' . number_format($get('from_balance'), 2)),

                Forms\Components\Textarea::make('remarks')
                    ->label('Remarks')
                    ->nullable(),

                Forms\Components\Hidden::make('request_by')
                    ->default(auth()->id()),

                Forms\Components\Hidden::make('company_id')
                    ->default(auth()->user()->company_id),

                Forms\Components\Select::make('approval_status')
                    ->label('Approval Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected'
                    ])
                    ->default('Pending')
                    ->hidden(fn () => !auth()->user()->hasRole('accounts_head'))
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fromUser.name')->label('From User'),
                Tables\Columns\TextColumn::make('toUser.name')->label('To User'),
                Tables\Columns\TextColumn::make('amount')->label('Amount')->money('INR'),
                Tables\Columns\TextColumn::make('approval_status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Requested On')->date(),
                Tables\Columns\TextColumn::make('approved_at')->label('Approved On')->date(),
            ])
            ->filters([
                SelectFilter::make('approval_status')
                    ->label('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->approval_status === 'Pending' && auth()->user()->hasRole('sales_operation_head')),

                Tables\Actions\DeleteAction::make()
                    ->hidden(), // Deletion is not allowed

            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function approveTransfer(InternalWalletTransfer $transfer)
    {
        $transfer->update([
            'approval_status' => 'Approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Log transactions
        WalletLog::create([
            'user_id' => $transfer->from_user_id,
            'amount' => -$transfer->amount,
            'credit_type' => 'internal_wallet_transfer',
            'description' => 'Transferred to ' . $transfer->receiver->name,
        ]);

        WalletLog::create([
            'user_id' => $transfer->to_user_id,
            'amount' => $transfer->amount,
            'credit_type' => 'internal_wallet_transfer',
            'description' => 'Received from ' . $transfer->sender->name,
        ]);
    }

    public static function rejectTransfer(InternalWalletTransfer $transfer)
    {
        $transfer->update([
            'approval_status' => 'Rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInternalWalletTransfers::route('/'),
            'create' => Pages\CreateInternalWalletTransfer::route('/create'),
            'view' => Pages\ViewInternalWalletTransfer::route('/{record}'),
            'edit' => Pages\EditInternalWalletTransfer::route('/{record}/edit'),
        ];
    }
}
