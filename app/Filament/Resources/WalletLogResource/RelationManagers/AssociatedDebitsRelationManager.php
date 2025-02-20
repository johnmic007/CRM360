<?php

namespace App\Filament\Resources\WalletLogResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\WalletLog;
use App\Models\TrainerVisit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class AssociatedDebitsRelationManager extends RelationManager
{
    protected static string $relationship = 'associatedDebits';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime(),
            ])
            ->filters([])
            ->actions([
                Action::make('Revert Payment')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        DB::transaction(function () use ($record) {
                            // Find the WalletLog entry for this transaction
                            $walletLog = WalletLog::find($record->id);
                            if (!$walletLog) {
                                return;
                            }

                            // dd($walletLog->wallet_logs);

                            $revertedBy = Auth::id();

                            // Find associated parent debit transactions
                            $associatedDebits = $walletLog->associatedDebits()->get();

                            $parentLog = WalletLog::where('id', $walletLog->wallet_logs)
                            ->first();

                            // dd($parentLog ,$walletLog );

                            $walletLog->wallet_logs = NULL;
                            $walletLog->save();

                            // Deduct the amount from this wallet log entry
                            $parentLog->balance += $walletLog->amount;
                            $parentLog->save();






                // Find the User to update their wallet_balance
                $user = User::find($walletLog->user_id);
                if (!$user) {
                    return; // User not found
                }

                // Update user's wallet_balance
                $user->wallet_balance += $walletLog->amount;
                $user->save();

                            // Create a new WalletLog entry for the deduction
                            WalletLog::create([
                                'user_id' => $walletLog->user_id,
                                'company_id' => $walletLog->company_id,
                                'trainer_visit_id' => $walletLog->trainer_visit_id,
                                'amount' => $walletLog->amount,
                                // 'balance' => $walletLog->balance,
                                'type' => 'credit',
                                'credit_type' => 'revert back payment',
                                'description' => 'Payment reverted from WalletLog ID: ' . $walletLog->id,
                                'reverted_by' => $revertedBy, // Save the user who reverted

                            ]);

                       

                            // Update TrainerVisit status to pending
                            TrainerVisit::where('id', $walletLog->trainer_visit_id)
                                ->update(['approval_status' => 'pending']);
                        });
                    })
                    ->color('danger')
                    ->icon('heroicon-o-arrow-path'),
            ]);
    }
}
