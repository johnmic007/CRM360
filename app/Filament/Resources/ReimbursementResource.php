<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReimbursementResource\Pages;
use App\Filament\Resources\SchoolReportResource\Pages\ViewReimbursements;
use App\Models\Reimbursement;
use App\Models\User;
use App\Models\TrainerVisit;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ReimbursementResource extends Resource
{
    protected static ?string $model = Reimbursement::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Reimbursements';

    protected static ?string $navigationGroup = 'Finance Management';

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasRole(['admin']);

    }
    

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Select::make('trainer_visit_id')
                    ->label('Trainer Visit')
                    ->options(TrainerVisit::all()->pluck('id', 'id'))
                    ->searchable()
                    ->required(),

                Select::make('user_id')
                    ->label('User')
                    ->options(User::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                TextInput::make('amount_due')
                    ->label('Total Expense')
                    ->numeric()
                    ->required(),

                TextInput::make('amount_covered')
                    ->label('Covered Amount')
                    ->numeric()
                    ->required(),

                TextInput::make('amount_remaining')
                    ->label('Remaining Amount')
                    ->numeric()
                    ->required(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'completed' => 'completed',
                    ])
                    ->required(),

                Textarea::make('notes')
                    ->label('Notes')
                    ->placeholder('Enter additional details...')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('trainer_visit_id')
                //     ->label('Trainer Visit')
                //     ->sortable()
                //     ->searchable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('amount_due')
                    ->label('Total Expense')
                    ->sortable(),

                TextColumn::make('amount_covered')
                    ->label('Covered Amount')
                    ->sortable(),

                TextColumn::make('amount_remaining')
                    ->label('Remaining Amount')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),

                
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->paginated([10, 25,]);

           
    }

    public static function getRelations(): array
    {
        return [
            // Define any relation managers here
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReimbursements::route('/'),
            'create' => Pages\CreateReimbursement::route('/create'),
            'edit' => Pages\EditReimbursement::route('/{record}/edit'),
            'view' => Pages\ViewReimbursements::route('/{record}'),

        ];
    }
}
