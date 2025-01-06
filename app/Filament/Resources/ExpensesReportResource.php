<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpensesReportResource\Pages;
use App\Filament\Resources\ExpensesReportResource\RelationManagers;
use App\Models\ExpensesReport;
use App\Models\TrainerVisit;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpensesReportResource extends Resource
{
    protected static ?string $model = TrainerVisit::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';

    
    protected static ?string $navigationLabel = 'Expense Report';

    protected static ?string $navigationGroup = 'Reports';


    protected static ?string $pluralLabel = 'Expense Report';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'sales_operation' , 'sales_operation_head' , 'head' , 'zonal_manager' , 'regional_manager' ]);
    }


    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('Trainer Details')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Trainer')
                        ->options(\App\Models\User::pluck('name', 'id')->toArray())
                        ->searchable()
                        ->required()
                        ->placeholder('Select a Trainer'),
                ])
                ->columns(1),

            Forms\Components\Section::make('Visit Details')
                ->schema([
                    Forms\Components\DatePicker::make('visit_date')
                        ->label('Visit Date')
                        ->required()
                        ->placeholder('Select the date of visit'),

                    Forms\Components\TextInput::make('travel_mode')
                        ->label('Travel Mode')
                        ->placeholder('Enter the travel mode (e.g., Car, Bike)')
                        ->required(),

                    Forms\Components\TextInput::make('distance_traveled')
                        ->label('Distance Traveled (KM)')
                        ->numeric()
                        ->placeholder('Enter the distance traveled')
                        ->required(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Expense Details')
                ->schema([
                    Forms\Components\TextInput::make('travel_expense')
                        ->label('Travel Expense')
                        ->numeric()
                        ->placeholder('Enter the travel expense')
                        ->required(),

                    Forms\Components\TextInput::make('food_expense')
                        ->label('Food Expense')
                        ->numeric()
                        ->placeholder('Enter the food expense')
                        ->required(),

                    Forms\Components\TextInput::make('total_expense')
                        ->label('Total Expense')
                        ->numeric()
                        ->placeholder('This will be auto-calculated')
                        ->disabled()
                        ->helperText('Total Expense = Travel Expense + Food Expense'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Approval and Verification')
                ->schema([
                    Forms\Components\Select::make('approval_status')
                        ->label('Approval Status')
                        ->options([
                            'approved' => 'Approved',
                            'pending' => 'Pending',
                            'rejected' => 'Rejected',
                        ])
                        ->required()
                        ->placeholder('Select approval status'),

                    Forms\Components\Select::make('verify_status')
                        ->label('Verification Status')
                        ->options([
                            'verified' => 'Verified',
                            'unverified' => 'Unverified',
                        ])
                        ->required()
                        ->placeholder('Select verification status'),

                    Forms\Components\Textarea::make('clarification_question')
                        ->label('Clarification Question')
                        ->placeholder('Enter any clarification question')
                        ->rows(2),

                    Forms\Components\Textarea::make('clarification_answer')
                        ->label('Clarification Answer')
                        ->placeholder('Enter the clarification answer')
                        ->rows(2),
                ])
                ->columns(2),
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

                Tables\Filters\Filter::make('visit_date')
                ->form([
                    Forms\Components\DatePicker::make('date')
                        ->label('Visit Date'),
                ])
                ->query(function (Builder $query, array $data) {
                    // If a date is selected, filter by that specific date
                    return $query->when($data['date'], function ($query, $date) {
                        $query->whereDate('visit_date', $date);
                    });
                })
                ->indicateUsing(function (array $data) {
                    // Check if 'date' exists in the filter data
                    if (!empty($data['date'])) {
                        return 'Date: ' . \Carbon\Carbon::parse($data['date'])->format('d-m-Y');
                    }
            
                    return null; // No indication if no date is selected
                }),            

                SelectFilter::make('approval_status')
                ->label('Approval Status')
                ->options([
                    'approved' => 'Approved',
                    'pending'  => 'Pending',
                    'rejected' => 'Rejected',
                ])
                ->attribute('approval_status'), // Tells Filament which column/attribute to filter
                // or you can use ->query(...) if you need custom logic

            /**
             * (C) Filter by Verify Status
             */
            SelectFilter::make('verify_status')
                ->label('Verify Status')
                ->options([
                    'verified'   => 'Verified',
                    'pending'  => 'Pending',
                ])
                ->attribute('verify_status'),

                
                SelectFilter::make('selected_user')
                ->label('User Team Visits') // Shortened label
                ->options(function () {
                    // Fetch users with specific roles (e.g., 'BDA' and 'BDM')
                    return User::role(['zonal_manager', 'bdm', 'regional_manager']) // Use the `role` method from Spatie's package
                        ->pluck('name', 'id') // Fetch users' names and IDs
                        ->all();
                })
                ->searchable()
                ->query(function (Builder $query, $data) {
                    // Check if the 'value' key exists in the data and retrieve its value
                    if (empty($data['value'])) {
                        // If no value is provided, skip the filter logic
                        return;
                    }

                    $selectedUserId = $data['value']; // Extract the selected user ID

                    // Fetch the selected user
                    $selectedUser = User::find($selectedUserId);

                    if ($selectedUser) {
                        // Fetch subordinate IDs
                        try {
                            $subordinateIds = $selectedUser->getAllSubordinateIds();
                            $subordinateIds[] = $selectedUser->id; // Include the selected user's ID

                            $query->whereIn('user_id', $subordinateIds); // Apply filter
                        } catch (\Exception $e) {
                            // Log or handle any errors
                            logger()->error('Error fetching subordinate IDs:', ['message' => $e->getMessage()]);
                        }
                    } else {
                        // Log when the selected user cannot be found
                        logger()->warning('User not found for selected ID:', ['user_id' => $selectedUserId]);
                    }
                }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListExpensesReports::route('/'),
            'create' => Pages\CreateExpensesReport::route('/create'),
            'view' => Pages\ViewExpensesReport::route('/{record}'),
            'edit' => Pages\EditExpensesReport::route('/{record}/edit'),
        ];
    }
}
