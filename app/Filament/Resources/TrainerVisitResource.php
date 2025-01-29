<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainerVisitResource\Pages;
use App\Filament\Resources\TrainerVisitResource\RelationManagers\PostsRelationManager;
use App\Filament\Resources\VisitEntryResource\RelationManagers\SchoolVisitRelationManager;
use App\Models\TrainerVisit;
use App\Models\User;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;


class TrainerVisitResource extends Resource
{
    protected static ?string $model = TrainerVisit::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    // protected static ?string $navigationLabel = 'Expenses Logs';

    // protected static ?string $pluralLabel = 'Expenses Logs';

    protected static ?string $navigationGroup = 'Approvals';

    // public static function canViewAny(): bool
    // {
    //     return !auth()->user()->hasRole('company');
    // }






    public static function canEdit($record): bool
    {
        // Allow edit only if the user is the owner of the record
        // return $record->user_id === auth()->id();

        return auth()->user()->hasRole(['admin', 'sales_operation_head']);
    }


    public static function getModelLabel(): string
    {
        $user = auth()->user();

        // Check if user has BDA or BDM role
        if (!$user->hasRole(['admin', 'sales_operation'])) {
            return 'Expenses Logs';
        }

        // Return a default label or empty string if you want no label otherwise
        return 'Expenses Requests';
    }


    public static function canCreate(): bool
    {
        return !auth()->user()->hasAnyRole(['admin', 'sales_operation', 'company']);
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole(['admin']);
    }





    public static function form(Forms\Form $form): Forms\Form
    {
        $user = auth()->user();

        return $form
            ->schema([
                Select::make('verify_status')
                    ->label('Verification Status ')
                    ->options([
                        'pending' => 'Pending',
                        'clarification' => 'Need Clarification',
                        'verified' => 'verified',
                        'answered' => 'anwered'
                    ])
                    ->default('pending')
                    ->disabled()
                    ->reactive()
                    ->live(),

                // Section for Clarification
                Forms\Components\Section::make('Clarification Details')
                    ->description('Provide clarification .')
                    ->schema([
                        TextInput::make('clarification_question')
                            ->label('Clarification Question')
                            ->placeholder('Enter the clarification question...')
                            ->disabled()
                            ->visible(fn($get) => $get('verify_status') === 'answered'),

                        TextInput::make('clarification_answer')
                            ->label('Clarification Answer')
                            ->placeholder('Provide your answer...')
                            ->readOnly()
                            ->required(fn($get) => $get('verify_status') === 'answered')
                            ->visible(fn($get) => $get('verify_status') === 'answered'),
                    ])
                    ->hidden(fn($get) => $get('verify_status') !== 'clarification'),


                Forms\Components\Card::make()
                    ->schema([



                        Hidden::make('user_id')
                            ->default(auth()->id())
                            ->required(),

                        Hidden::make('company_id')
                            ->default(fn() => auth()->user()->company_id)
                            ->required(),

                        Select::make('user_id')
                            ->label('Name')
                            ->disabled()
                            ->relationship('user', 'name')
                            ->required(),


                        // Select::make('school_id')
                        // ->label('School')
                        // ->options(function ($record) {
                        //     $userId = auth()->id();
                        //     $todayVisitedSchools = \App\Models\SalesLeadStatus::query()
                        //         ->where('visited_by', $userId)
                        //         ->whereDate('created_at', now()->toDateString())
                        //         ->with('school') // Load the school relationship
                        //         ->get()
                        //         ->pluck('school.name', 'school.id'); // Get today's visited schools

                        //     if ($record && $record->school_id) {
                        //         // Include the selected school even if it wasn't visited today
                        //         $selectedSchool = \App\Models\School::query()
                        //             ->where('id', $record->school_id)
                        //             ->pluck('name', 'id');

                        //         return $selectedSchool->union($todayVisitedSchools);
                        //     }

                        //     return $todayVisitedSchools;
                        // })
                        // ->required()
                        // ->searchable()
                        // ->multiple()
                        // ->visible(fn($record) => $record === null) // Only visible when creating a new record

                        // ->helperText('Select a school. Shows today\'s visited schools but includes already selected schools if editing.')

                        // ->disabled(fn($record) => $record && $record->verify_status === 'verified')
                        // ->helperText('Only shows schools visited today.')
                        // ->preload()
                        // ->default(fn($record) => $record && $record->school_id ? [$record->school_id] : []), // Pre-select school if editing





                        DatePicker::make('visit_date')
                            ->label('Visit Date')
                            ->default(now())
                            ->disabled(fn($record) => $record && $record->verify_status === 'verified' && !auth()->user()->hasRole('admin')) // Allow admin to edit
                            ->required(),

                        TextInput::make('total_expense')
                            ->numeric()
                            ->hidden(fn(callable $get) => $get('travel_type') !== 'extra_expense')
                            ->readOnly(fn() => !auth()->user()->hasAnyRole(['sales_operation', 'sales_operation_head'])),

                        Textarea::make('description')
                            ->hidden(fn(callable $get) => $get('travel_type') !== 'extra_expense')
                            ->readOnly(),


                        FileUpload::make('travel_bill')
                            ->required()
                            ->multiple()
                        ->disk('s3')
                            ->directory('CRM')
                            ->hidden(fn(callable $get) => $get('travel_type') !== 'extra_expense')

                            ->disabled(fn($record) => $record && $record->verify_status === 'verified') // Ensure $record is not null

                            ->helperText('Upload the bill for bus/train travel.'),

                        Select::make('travel_type')
                            ->label('Travel Type')
                            ->options([
                                'own_vehicle' => 'Travel by Own Vehicle',
                                'with_colleague' => 'Travel with Colleague',
                                'with_head' => 'Travel with Head',

                            ])
                            ->reactive()
                            ->required()
                            ->hidden(fn(callable $get) => $get('travel_type') == 'extra_expense')
                            ->disabled(fn($record) => $record && $record->verify_status === 'verified') // Ensure $record is not null

                            ->helperText('Select how you traveled. Additional fields will appear based on your choice.')
                            ->afterStateUpdated(function ($state, $set) {
                                // Reset all related fields when travel_type changes
                                if ($state === 'own_vehicle') {
                                    $set('travel_mode', null);
                                    $set('starting_meter_photo', null);
                                    $set('starting_km', null);
                                    $set('ending_meter_photo', null);
                                    $set('ending_km', null);
                                    $set('distance_traveled', 0);
                                    $set('travel_expense', 0);
                                } elseif ($state === 'with_colleague') {
                                    $set('travel_bill', null);
                                    $set('travel_expense', null);
                                    $set('food_expense', Setting::getFoodExpenseRate());
                                }
                            }),
                    ])
                    ->columns(2)
                    ->columnSpan('full'),

                // Fields for "Travel by Own Vehicle"
                Forms\Components\Section::make('Own Vehicle Details')
                    ->description('Provide details about your travel using your own vehicle.')
                    ->schema([
                        Select::make('travel_mode')
                            ->label('Travel Mode')
                            ->options([
                                'car' => 'Car',
                                'bike' => 'Bike',
                            ])
                            ->reactive()
                            ->disabled(fn($record) => $record && $record->verify_status === 'verified') // Ensure $record is not null

                            ->required(),

                        FileUpload::make('starting_meter_photo')
                            ->label('Starting Meter Photosss')
                        ->disk('s3')
                            ->directory('CRM')
                            ->disabled(fn($record) => $record && $record->verify_status === 'verified') 
                            ->helperText('Upload a clear photo of the starting meter.')
                            ->required(),




                        // FileUpload::make('starting_meter_photo')
                        //     ->label('Starting Meter Photo')
                        //     ->disk('s3')
                        //     ->directory('CRM')
                        //     ->helperText('Upload a clear photo of the starting meter.')
                        //     ->disabled(fn($record) => $record && $record->verify_status === 'verified')
                        //     ->visibility('public') // Ensure proper permissions for S3 files
                        //     ->afterStateHydrated(function ($state, $component) {
                        //         if (is_string($state) && !str_starts_with($state, 'CRM/')) {
                        //             // Temporarily adjust the state for display without saving it permanently
                        //             $component->state('CRM/' . $state);
                        //         }
                        //     })
                        //     ->required(),


                        TextInput::make('starting_km')
                            ->label('Starting Kilometer')
                            ->numeric()
                            ->required()
                            ->reactive()

                            ->disabled(fn($record) => $record && $record->verify_status === 'verified') // Ensure $record is not null

                            ->afterStateUpdated(function ($state, $set, $get) {
                                $endingKm = $get('ending_km');
                                if ($endingKm !== null && $state !== null) {
                                    $distance = max(0, $endingKm - $state);
                                    $set('distance_traveled', $distance);

                                    // Calculate travel expense
                                    $travelMode = $get('travel_mode');
                                    $rate = $travelMode === 'car'
                                        ? Setting::getCarRate()
                                        : Setting::getBikeRate();
                                    $set('travel_expense', $rate * $distance);
                                }
                            }),

                        FileUpload::make('ending_meter_photo')
                            ->disabled(fn($record) => $record && $record->verify_status === 'verified') // Ensure $record is not null
                        ->disk('s3')
                            ->directory('CRM')

                            ->label('Ending Meter Photo'),

                        TextInput::make('ending_km')
                            ->label('Ending Kilometer')
                            ->numeric()
                            ->required()

                            ->reactive()
                            ->disabled(fn($record) => $record && $record->verify_status === 'verified') // Ensure $record is not null

                            ->afterStateUpdated(function ($state, $set, $get) {
                                $startingKm = $get('starting_km');
                                if ($startingKm !== null && $state !== null) {
                                    $distance = max(0, $state - $startingKm);
                                    $set('distance_traveled', $distance);

                                    // Calculate travel expense
                                    $travelMode = $get('travel_mode');
                                    $rate = $travelMode === 'car'
                                        ? Setting::getCarRate()
                                        : Setting::getBikeRate();
                                    $travelExpense = $rate * $distance;

                                    $set('travel_expense', $rate * $distance);

                                    $foodExpense = Setting::getFoodExpenseRate();

                                    // Update total expense
                                    $set('total_expense', $travelExpense + $foodExpense);
                                }
                            }),

                        TextInput::make('distance_traveled')
                            ->label('Distance Traveled')
                            ->numeric()
                            ->readOnly()
                            ->disabled(fn($record) => $record && $record->verify_status === 'verified') // Ensure $record is not null

                            ->default(0),

                        TextInput::make('travel_expense')
                            ->label('Travel Expense')
                            ->numeric()
                            ->readOnly()
                            ->default(0),

                        TextInput::make('food_expense')
                            ->label('Food Expense')
                            ->numeric()
                            ->readOnly()
                            ->default(Setting::getFoodExpenseRate()),

                        TextInput::make('total_expense')
                            ->numeric()
                            ->readOnly(),
                    ])
                    ->columns(2)
                    ->hidden(fn($get) => $get('travel_type') !== 'own_vehicle'),

                // Fields for "Travel with Colleague"
                Forms\Components\Section::make('Colleague Vehicle Details')
                    ->description('Provide details about your travel with a colleague.')
                    ->schema([
                        FileUpload::make('travel_bill')
                            ->label('Upload Travel Bill (Bus/Train)')
                            ->required()
                        ->disk('s3')
                            ->directory('CRM')

                            ->disabled(fn($record) => $record && $record->verify_status === 'verified') // Ensure $record is not null

                            ->helperText('Upload the bill for bus/train travel.'),




                        TextInput::make('travel_expense')
                            ->label('Travel Expense')
                            ->numeric()
                            ->required()
                            ->disabled(fn($record) => $record && $record->verify_status === 'verified') // Ensure $record is not null

                            ->helperText('Enter the expense amount for colleague travel.')
                            ->afterStateUpdated(function ($state, callable $set) {
                                // $state here is the updated 'travel_expense'
                                // Add it to the food expense rate and set 'total_expense'
                                $set('total_expense', $state + Setting::getFoodExpenseRate());
                            }),

                        TextInput::make('food_expense')
                            ->label('Food Expense')
                            ->numeric()
                            ->readOnly()
                            ->default(Setting::getFoodExpenseRate()),

                        TextInput::make('total_expense')
                            ->numeric()
                            ->readOnly(),
                    ])
                    ->columns(2)
                    ->hidden(fn($get) => $get('travel_type') !== 'with_colleague'),


                TextInput::make('total_expense')
                    ->hidden(fn($get) => $get('travel_type') !== 'with_head')

                    ->readOnly(),




                Select::make('user_travel_with')
                    ->label('Users Travel With')
                    ->disabled()
                    ->hidden(fn($get) => $get('travel_type') !== 'with_head')
                    ->options(\App\Models\User::pluck('name', 'id')->toArray()) // Provide a list of users
                    ->multiple() // Allow multiple selections
                    ->searchable(),

                Forms\Components\FileUpload::make('files')
            ->disk('s3')
                ->directory('CRM')
                    ->label('Upload School Images') // Clear and descriptive label
                    ->hidden(fn(callable $get) => $get('travel_type') == 'extra_expense')

                    ->required() // Makes the field mandatory
                    ->multiple() // Allows multiple files to be uploaded
                    ->disabled(fn($record) => $record && $record->verify_status === 'verified') // Ensure $record is not null
                    ->maxFiles(10) // Limit the maximum number of files (optional, adjust as needed)
                    ->helperText('Upload up to 10 school images in JPEG or PNG format.'), // Enhanced helper text

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Name'),
                // TextColumn::make('school.name')->label('School'),
                TextColumn::make('visit_date')->label('Visit Date')->date(),
                TextColumn::make('travel_mode')->label('Travel Mode'),
                // TextColumn::make('starting_km')->label('Starting Kilometer'),
                // TextColumn::make('ending_km')->label('Ending Kilometer'),
                TextColumn::make('distance_traveled')->label('Distance (km)'),
                TextColumn::make('total_expense')->label('Total Expense')->money('INR'),
                Tables\Columns\TextColumn::make('approval_and_verification_status')
                    ->label('Approval & Verification Status')
                    ->badge() // Adds badge styling
                    ->getStateUsing(function ($record) {
                        $approvalStatus = ucfirst($record->approval_status); // Capitalize the first letter
                        $verifyStatus = ucfirst($record->verify_status);     // Capitalize the first letter
                        return "{$approvalStatus} / {$verifyStatus}";
                    })
                    ->colors([
                        'danger' => fn($state) => str_contains($state, 'Rejected') || str_contains($state, 'Pending'),
                        'success' => fn($state) => str_contains($state, 'Approved') && str_contains($state, 'Verified'),
                        'warning' => fn($state) => str_contains($state, 'Clarification'),
                        'primary' => fn($state) => str_contains($state, 'Pending'),
                    ])
                    ->sortable(),

                // TextColumn::make('approval_status')
                //     ->badge()
                //     ->colors([
                //         'primary' => 'Pending',
                //         'success' => 'approved',
                //         'danger' => 'rejected',
                //     ])
                //     ->sortable(),


                //     Tables\Columns\TextColumn::make('verify_status')
                //     ->label('Verification Status')
                //     ->badge() // Adds the badge styling
                //     ->visible(fn() => !auth()->user()->hasAnyRole(['accounts', 'accounts_head']))
                //     ->Colors([
                //         'danger' => 'pending',           // Red for 'Pending'
                //         'warning' => 'clarification',   // Yellow for 'Need Clarification'
                //         'success' => 'verified',        // Green for 'Verified'
                //     ]),                



                Tables\Columns\IconColumn::make('approved_by')
                    ->label('Approved By')
                    ->tooltip(fn($state) => $state ? User::find($state)?->name ?? 'User Not Found' : 'Pending')
                    ->icon(fn($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-clock')
                    ->colors([
                        'success' => fn($state) => $state !== null, // Green for approved
                        'secondary' => fn($state) => $state === null, // Gray for pending
                    ])
                    ->extraAttributes([
                        'class' => 'cursor-pointer',
                    ]),

                TextColumn::make('remarks')->label('Remark'),



            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('download_pdf')
                        ->label('Download PDF')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->url(fn(TrainerVisit $record) => route('trainer-visit.download', $record->id))
                        ->openUrlInNewTab(), // Opens the PDF in a new tab
                ]),
            ])
            ->filters([
                Tables\Filters\Filter::make('visit_date')
                    ->label('Visit Date')
                    ->form([
                        Forms\Components\DatePicker::make('date')
                            ->label('Visit Date')
                            ->placeholder('Choose a date'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when(
                            $data['date'],
                            fn($q) => $q->whereDate('visit_date', $data['date'])
                        );
                    })
                    ->indicateUsing(function (array $data) {
                        if (!empty($data['date'])) {
                            $date = \Carbon\Carbon::parse($data['date']); // Parse the date into Carbon/DateTime
                            return 'Visit Date: ' . $date->format('M d, Y');
                        }
                        return null;
                    }),

                SelectFilter::make('travel_type')
                    ->label('Travel Type')
                    ->options([
                        'own_vehicle' => 'Travel by Own Vehicle',
                        'with_colleague' => 'Travel with Colleague',
                    ]),

                SelectFilter::make('travel_mode')
                    ->label('Travel Type')
                    ->options([
                        'car' => 'Car',
                        'bike' => 'Bike',
                    ]),


                SelectFilter::make('selected_user')
                    ->label('User') // Updated label
                    ->options(function () {
                        // Fetch all users except those with the 'admin' role
                        return User::whereDoesntHave('roles', function ($query) {
                            $query->where('name', 'admin');
                        })
                            ->pluck('name', 'id') // Fetch users' names and IDs
                            ->all();
                    })
                    ->searchable()
                    ->query(function (Builder $query, $data) {
                        if (!empty($data['value'])) {
                            // Filter by the selected user's ID
                            $query->where('user_id', $data['value']);
                        }
                    }),

            ]);

        // ->bulkActions([
        //     Tables\Actions\BulkAction::make('downloadPdf')
        //         ->label('Download as PDF')
        //         ->action(fn($records) => self::downloadPdf($records)),
        // ]);
    }

    public static function getRelations(): array
    {
        return [
            SchoolVisitRelationManager::class,

        ];
    }

    public static function downloadPdf($records)
    {
        $data = $records->map(function ($record) {
            return [
                'Name' => $record->user->name,
                'Visit Date' => $record->visit_date,
                'Travel Mode' => $record->travel_mode,
                'Starting KM' => $record->starting_km,
                'Ending KM' => $record->ending_km,
                'Distance' => $record->distance_traveled,
                'Total Expense' => $record->total_expense,
                'Status' => $record->approval_status,
                'Approved By' => $record->approved_by ? User::find($record->approved_by)->name : 'Pending',
                // 'Starting Meter Photo' => $record->starting_meter_photo ? base64_encode(file_get_contents(storage_path('app/public/' . $record->starting_meter_photo))) : null,
                // 'Ending Meter Photo' => $record->ending_meter_photo ? base64_encode(file_get_contents(storage_path('app/public/' . $record->ending_meter_photo))) : null,
                // 'Travel Bill' => $record->travel_bill ? base64_encode(file_get_contents(storage_path('app/public/' . $record->travel_bill))) : null,
            ];
        });

        $pdf = Pdf::loadView('pdf.trainer-visits', ['data' => $data]);
        return $pdf->download('trainer-visits.pdf');
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainerVisits::route('/'),
            'create' => Pages\CreateTrainerVisit::route('/create'),
            'edit' => Pages\EditTrainerVisit::route('/{record}/edit'),
            'view' => Pages\ViewTrainerVisit::route('/{record}'),

        ];
    }
}
