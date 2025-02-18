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
    use Filament\Tables\Table;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\SoftDeletingScope;
    use Filament\Tables\Filters\Filter;
    use Filament\Forms\Components\DatePicker;
    use Filament\Forms\Components\Select;



    class ExpensesReportResource extends Resource
    {
        protected static ?string $model = TrainerVisit::class;

        protected static ?string $navigationIcon = 'heroicon-o-arrow-trending-up';


        protected static ?string $navigationLabel = 'Expense Report';

        protected static ?string $navigationGroup = 'Reports';


        protected static ?string $pluralLabel = 'Expense Report';

        public static function canViewAny(): bool
        {
            return auth()->user()->hasRole(['admin', 'head', 'sales_head', 'sales_operation', 'sales_operation_head', 'zonal_manager', 'regional _manager', 'head' , 'bdm' , 'bda']);
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
                    // Visit Date Filter
                    Tables\Filters\Filter::make('visit_date')
                        ->form([
                            Forms\Components\DatePicker::make('date')
                                ->label('Visit Date'),
                        ])
                        ->query(function (Builder $query, array $data) {
                            return $query->when($data['date'], function ($query, $date) {
                                $query->whereDate('visit_date', $date);
                            });
                        })
                        ->indicateUsing(function (array $data) {
                            return !empty($data['date'])
                                ? 'Date: ' . \Carbon\Carbon::parse($data['date'])->format('d-m-Y')
                                : null;
                        }),

                    // Start Date Filter (Applies to table)
                    Filter::make('start_date')
                        ->label('Start Date')
                        ->form([
                            DatePicker::make('start_date')->placeholder('Select a start date'),
                        ])
                        ->query(function (Builder $query, array $data) {
                            return $query->when($data['start_date'], function ($query, $startDate) {
                                $query->whereDate('visit_date', '>=', $startDate);
                            });
                        })
                        ->indicateUsing(fn (array $data) => !empty($data['start_date']) ? 'Start Date: ' . $data['start_date'] : null),

                    // End Date Filter (Applies to table)
                    Filter::make('end_date')
                        ->label('End Date')
                        ->form([
                            DatePicker::make('end_date')->placeholder('Select an end date'),
                        ])
                        ->query(function (Builder $query, array $data) {
                            return $query->when($data['end_date'], function ($query, $endDate) {
                                $query->whereDate('visit_date', '<=', $endDate);
                            });
                        })
                        ->indicateUsing(fn (array $data) => !empty($data['end_date']) ? 'End Date: ' . $data['end_date'] : null),

                    // Exclude Users Filter
                    Filter::make('exclude_users')
                        ->label('Exclude Selected Users')
                        ->form([
                            Select::make('exclude_users')
                                ->multiple()
                                ->options(User::pluck('name', 'id')->toArray())
                                ->searchable()
                                ->placeholder('Select users to exclude'),
                        ])
                        ->query(function (Builder $query, array $data) {
                            if (!empty($data['exclude_users'])) {
                                $query->whereNotIn('user_id', $data['exclude_users']);
                            }
                        })
                        ->indicateUsing(function (array $data) {
                            return empty($data['exclude_users']) ? null : 'Excluded Users: ' . User::whereIn('id', $data['exclude_users'])->pluck('name')->implode(', ');
                        }),

                    // Include Users Filter
                    Filter::make('include_users')
                        ->label('Include Selected Users')
                        ->form([
                            Select::make('include_users')
                                ->multiple()
                                ->options(User::pluck('name', 'id')->toArray())
                                ->searchable()
                                ->placeholder('Select users to include'),
                        ])
                        ->query(function (Builder $query, array $data) {
                            if (!empty($data['include_users'])) {
                                $query->whereIn('user_id', $data['include_users']);
                            }
                        })
                        ->indicateUsing(function (array $data) {
                            return empty($data['include_users']) ? null : 'Included Users: ' . User::whereIn('id', $data['include_users'])->pluck('name')->implode(', ');
                        }),

                    // Approval Status Filter
                    Filter::make('approval_status')
                        ->label('Approval Status')
                        ->form([
                            Select::make('approval_status')
                                ->options([
                                    'approved' => 'Approved',
                                    'pending' => 'Pending',
                                    'rejected' => 'Rejected',
                                ])
                                ->placeholder('All'),
                        ])
                        ->query(function (Builder $query, array $data) {
                            return $query->when($data['approval_status'], function ($query, $status) {
                                $query->where('approval_status', $status);
                            });
                        })
                        ->indicateUsing(fn (array $data) => !empty($data['approval_status']) ? 'Approval Status: ' . $data['approval_status'] : null),

                    // User Filter (Zonal Manager, BDM, Regional Manager)
                    Filter::make('user_id')
                        ->label('Filter by User')
                        ->form([
                            Select::make('user_id')
                                ->label('Filter by User')
                                ->options(function () {
                                    return User::whereHas('roles', function ($query) {
                                        $query->whereIn('name', ['zonal_manager', 'bdm', 'regional_manager']);
                                    })->pluck('name', 'id')->toArray();
                                })
                                ->searchable(),
                        ])
                        ->query(function (Builder $query, array $data) {
                            return $query->when($data['user_id'], function ($query, $userId) {
                                $query->where('user_id', $userId);
                            });
                        })
                        ->indicateUsing(fn (array $data) => !empty($data['user_id']) ? 'User: ' . User::find($data['user_id'])->name : null),
                ])

                ->actions([
                    Tables\Actions\ViewAction::make(),
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
                'index' => Pages\ListExpensesReports::route('/'),
                'create' => Pages\CreateExpensesReport::route('/create'),
                'view' => Pages\ViewExpensesReport::route('/{record}'),
                'edit' => Pages\EditExpensesReport::route('/{record}/edit'),
            ];
        }
    }
