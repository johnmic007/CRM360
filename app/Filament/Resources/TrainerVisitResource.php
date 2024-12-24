<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrainerVisitResource\Pages;
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


class TrainerVisitResource extends Resource
{
    protected static ?string $model = TrainerVisit::class;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    // protected static ?string $navigationLabel = 'Expenses Logs';

    // protected static ?string $pluralLabel = 'Expenses Logs';

    protected static ?string $navigationGroup = 'Approvals';

    public static function getNavigationBadge(): ?string
    {

        if (!auth()->user()->hasRole(['admin', 'sales'])) {
            return null; // Do not show the badge if the user is not an admin or sales role
        }
        // Count trainer visits where 'approved_by' is null
        $pendingApprovals = TrainerVisit::whereNull('approved_by')->count();

        // Return the count or null if no pending approvals
        return $pendingApprovals > 0 ? (string) $pendingApprovals : null;
    }


    public static function getModelLabel(): string
    {
        $user = auth()->user();

        // Check if user has BDA or BDM role
        if (!$user->hasRole(['admin', 'sales'])) {
            return 'Expenses Logs';
        }

        // Return a default label or empty string if you want no label otherwise
        return 'Expenses Requests';
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
                    'completed' => 'Completed',
                ])
                ->default('pending')
                ->disabled()
                ->reactive()
                ->live()
                ->extraAttributes(function (callable $get) use ($user) {
                    $statusColors = [
                        'pending' => 'background-color: #ffeb3b; color: #000;',
                        'clarification' => 'background-color: #ff9800; color: #fff;',
                        'completed' => 'background-color: #4caf50; color: #fff;',
                    ];

                    $status = $get('verify_status') ?? 'pending';
                    $baseStyle = $statusColors[$status] ?? 'background-color: #f8f9fa; color: #000;';

                    if ($user->hasAnyRole(['admin', 'sales'])) {
                        $baseStyle .= ' border: 2px solid #4CAF50; font-weight: bold;';
                    }

                    return ['style' => $baseStyle];
                }),

            // Section for Clarification
            Forms\Components\Section::make('Clarification Details')
                ->description('Provide clarification .')
                ->schema([
                    TextInput::make('clarification_question')
                        ->label('Clarification Question')
                        ->placeholder('Enter the clarification question...')
                        ->disabled()
                        ->visible(fn($get) => $get('verify_status') === 'clarification'),

                    TextInput::make('clarification_answer')
                        ->label('Clarification Answer')
                        ->placeholder('Provide your answer...')
                        ->required(fn($get) => $get('verify_status') === 'clarification')
                        ->visible(fn($get) => $get('verify_status') === 'clarification'),
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

                        Select::make('school_id')
                            ->label('School')
                            ->options(function () {
                                $userId = auth()->id();

                                // Fetch schools where the authenticated user is in visited_by and created today
                                return \App\Models\SalesLeadStatus::query()
                                    ->where('visited_by', $userId)
                                    ->whereDate('created_at', now()->toDateString())
                                    ->with('school') // Ensure the school relationship is loaded
                                    ->get()
                                    ->pluck('school.name', 'school.id'); // Adjust according to your relationships
                            })
                            ->required()
                            ->multiple()
                            ->helperText('Only shows schools visited today.')
                            ->preload(),

                        DatePicker::make('visit_date')
                            ->label('Visit Date')
                            ->default(now())
                            ->required(),

                        Select::make('travel_type')
                            ->label('Travel Type')
                            ->options([
                                'own_vehicle' => 'Travel by Own Vehicle',
                                'with_colleague' => 'Travel with Colleague',
                            ])
                            ->reactive()
                            ->required()
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
                            ->required(),

                        FileUpload::make('starting_meter_photo')
                            ->label('Starting Meter Photo')
                            ->image()
                            ->visibility('public') // Ensures the file is publicly accessible.
                            ->directory('trainer-visits') // Specify the directory for uploads.
                            ->helperText('Upload a clear photo of the starting meter.')
                            ->previewable(true)
                            ->required(),


                        TextInput::make('starting_km')
                            ->label('Starting Kilometer')
                            ->numeric()
                            ->required()
                            ->reactive()
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
                            ->label('Ending Meter Photo'),

                        TextInput::make('ending_km')
                            ->label('Ending Kilometer')
                            ->numeric()
                            ->required()
                            ->reactive()
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
                            ->helperText('Upload the bill for bus/train travel.'),
                            

                        TextInput::make('travel_expense')
                            ->label('Travel Expense')
                            ->numeric()
                            ->required()
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


                    Forms\Components\FileUpload::make('files')
                    ->label('Upload School Images') // Clear and descriptive label
                    ->required() // Makes the field mandatory
                    ->multiple() // Allows multiple files to be uploaded
                    ->directory('school-images') // Define the upload directory
                    ->maxFiles(10) // Limit the maximum number of files (optional, adjust as needed)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg']) // Restrict file types (optional)
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
                TextColumn::make('starting_km')->label('Starting Kilometer'),
                TextColumn::make('ending_km')->label('Ending Kilometer'),
                TextColumn::make('distance_traveled')->label('Distance (km)'),
                TextColumn::make('total_expense')->label('Total Expense')->money('INR'),
                TextColumn::make('approval_status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'primary' => 'Pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->sortable(),


                    TextColumn::make('verify_status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('approved_by')->label('Approved By')
                    ->formatStateUsing(fn($state) => $state ? User::find($state)->name : 'Pending'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Download PDF')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn(TrainerVisit $record) => route('trainer-visit.download', $record->id))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('downloadPdf')
                    ->label('Download as PDF')
                    ->action(fn($records) => self::downloadPdf($records)),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
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
        ];
    }
}
