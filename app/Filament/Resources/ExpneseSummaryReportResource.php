<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpneseSummaryReportResource\Pages;
use App\Filament\Resources\ExpneseSummaryReportResource\RelationManagers;
use App\Models\ExpneseSummaryReport;
use App\Models\TrainerVisit;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpneseSummaryReportResource extends Resource
{
    protected static ?string $model = TrainerVisit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    protected static ?string $navigationLabel = 'Expense Report Summary';

    protected static ?string $navigationGroup = 'Reports';


    protected static ?string $pluralLabel = 'Expense Report Summary';


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
            // 1. Show the trainer’s name (related to `user`)
            Tables\Columns\TextColumn::make('user.name')
                ->label('Trainer Name')
                ->searchable()
                ->sortable(),

                Tables\Columns\TextColumn::make('user.wallet_balance')
                ->label('Cash in hand')
                ->searchable()
                ->sortable(),

            // 2. Visit Date
            Tables\Columns\TextColumn::make('visit_date')
                ->label('Visit Date')
                ->date('d M Y')   // or ->dateTime() if you want time also
                ->sortable(),

            // 3. Travel Mode
            Tables\Columns\TextColumn::make('travel_mode')
                ->label('Travel Mode')
                ->sortable(),

            // 4. Distance traveled
            Tables\Columns\TextColumn::make('distance_traveled')
                ->label('Distance (KM)')
                ->sortable(),

            // 5. Travel expense
            Tables\Columns\TextColumn::make('travel_expense')
                ->label('Travel Expense')
                ->money('inr', true)  // or your preferred currency / formatting
                ->sortable(),

       
                

            // 7. Total expense
            Tables\Columns\TextColumn::make('total_expense')
                ->label('Total Expense')
                ->money('inr', true)
                ->sortable(),

            // 8. Approval status
            Tables\Columns\BadgeColumn::make('approval_status')
                ->label('Approval Status')
                
                ->colors([
                    'primary',
                    'success' => 'approved',
                    'danger'  => 'rejected',
                    'warning' => 'pending',
                ])
                ->sortable(),

            // 9. Verification status
            Tables\Columns\BadgeColumn::make('verify_status')
                ->label('Verify Status')
              
                ->colors([
                    'success' => 'verified',
                    'danger'  => 'unverified',
                ])
                ->sortable(),

            // // 10. Clarification question/answer (optional)
            // Tables\Columns\TextColumn::make('clarification_question')
            //     ->label('Clarification Question')
            //     ->limit(40), // limit text in the table cell

            // Tables\Columns\TextColumn::make('clarification_answer')
            //     ->label('Clarification Answer')
            //     ->limit(40),
        ])
            ->filters([
                SelectFilter::make('user_id')
    ->label('Filter by User') // Label for filter
    ->options(function () {
        return User::role(['zonal_manager', 'bdm', 'regional_manager']) // Fetch users with specific roles
            ->pluck('name', 'id') // Return [id => name]
            ->toArray();
    })
    ->searchable()
    ->query(function (Builder $query, $data) {
        if (empty($data['value'])) {
            return; // Skip filtering if no user is selected
        }

        $selectedUserId = $data['value']; // Get selected user ID

        // Apply filter only for the selected user (No subordinates)
        $query->where('user_id', $selectedUserId);
    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25,]);

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
            'index' => Pages\ListExpneseSummaryReports::route('/'),
            'create' => Pages\CreateExpneseSummaryReport::route('/create'),
            'edit' => Pages\EditExpneseSummaryReport::route('/{record}/edit'),
        ];
    }
}
