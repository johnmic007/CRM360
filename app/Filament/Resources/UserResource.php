<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolResource\RelationManagers\LeadStatusesRelationManager;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\DealClosedByRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\IssuedBooksRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\SchoolCopyRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\WalletPaymentLogsRelationManager;
use App\Filament\Resources\WalletLogsResource\RelationManagers\UserRelationManager;
use App\Filament\Resources\WalletLogsResource\RelationManagers\WalletLogsRelationManager;
use App\Models\District;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Role;
use App\Models\State;
use App\Models\WalletLog;
use App\Models\WalletPaymentLogs;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationGroup = 'Users Management';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'sales' , 'head', 'zonal_manager', 'regional _manager', 'senior_manager', 'bdm']);
    }


    public static function getModelLabel(): string
    {
        $user = auth()->user();

        // Check if user has BDA or BDM role
        if ($user && $user->hasRole(['bda', 'bdm'])) {
            return 'Team';
        }

        // Return a default label or empty string if you want no label otherwise
        return 'Users';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // User name input
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                // Email input
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(
                        User::class,
                        'email',
                        ignoreRecord: true // This will ignore the current record when checking uniqueness
                    ),
                // Password input
                TextInput::make('password')
                    ->password()
                    // ->required()
                    ->dehydrated(fn($state) => $state ? bcrypt($state) : null),

                Select::make('company_id')
                    ->label('Company')
                    ->options(function () {
                        $user = auth()->user();

                        // Only show the field for admins
                        if (!$user || !$user->roles()->where('name', 'admin')->exists()) {
                            return [];
                        }

                        // Fetch all companies
                        return \App\Models\Company::pluck('name', 'id')->toArray();
                    })
                    ->required() // Make it required
                    ->hidden(fn() => !auth()->user()->roles()->where('name', 'admin')->exists()) // Hide if not admin
                    ->helperText('This field is visible only to Admin users.'),

                // District and Block allocation fields for admin and sales roles
                // Forms\Components\Select::make('districts')
                //     ->label('Allocate Districts')
                //     ->options(\App\Models\District::pluck('name', 'id'))
                //     ->multiple()
                //     ->preload()
                //     ->required()
                //     ->visible(fn() => auth()->user()->hasRole(['admin', 'sales']))
                //     ->helperText('Select the districts to allocate to this user.'),


                Forms\Components\Select::make('allocated_states')
                    ->label('Allocate States')
                    ->options(State::pluck('name', 'id'))
                    ->multiple()
                    ->preload()
                    ->required()
                    ->visible(fn() => auth()->user()->hasRole(['admin', 'sales']))
                    ->helperText('Select the blocks to allocate to this user.')
                    ->afterStateUpdated(fn(callable $set) => $set('allocated_districts', null)), // Reset district when state changes


                Forms\Components\Select::make('allocated_districts')
                    ->label('Allocate Districts')
                    ->options(function (callable $get) {
                        $stateId = $get('allocated_states');
                        if (!$stateId) {
                            return [];
                        }
                        // Fetch districts for the chosen state
                        return \App\Models\District::where('state_id', $stateId)->pluck('name', 'id')->toArray();
                    })->multiple()
                    ->preload()
                    ->required()
                    ->visible(fn() => auth()->user()->hasRole(['admin', 'sales']))
                    ->helperText('Select the blocks to allocate to this user.'),




                // Role selection with hierarchy logic
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->required()
                    ->preload()
                    ->options(function () {
                        $user = auth()->user();

                        // Admins can see all roles
                        if ($user->roles()->where('name', 'admin')->exists()) {
                            return Role::pluck('name', 'id');
                        }

                        // Fetch roles that are one level below the user's roles
                        $userRoleLevels = $user->roles->pluck('level');
                        $maxLevel = $userRoleLevels->min() ?? 0; // Fallback to 0 if no roles are found

                        return Role::where('level', '>', $maxLevel)->pluck('name', 'id');
                    })
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (!$state) {
                            $set('manager_id', null);
                            return;
                        }

                        // Fetch the selected role's level
                        $selectedRoles = Role::whereIn('id', $state)->pluck('level');
                        $minLevel = $selectedRoles->min(); // Get the lowest level if multiple roles are selected

                        // If the user is at the top level (Head), no manager is required
                        if ($minLevel === 1) {
                            $set('manager_id', null); // Reset the manager field
                        }
                    }),


                // Manager selection based on role hierarchy
                Select::make('manager_id')
                    ->label('Manager')
                    ->options(function (callable $get) {
                        $selectedRoles = $get('roles');
                        if (!$selectedRoles) {
                            return []; // No manager options if roles are not set
                        }

                        // Fetch the highest level of the selected role(s)
                        $selectedLevels = Role::whereIn('id', $selectedRoles)->pluck('level');
                        $minLevel = $selectedLevels->min();

                        // Fetch users with roles exactly one level above the current role
                        $allowedManagerLevel = $minLevel - 1;

                        return User::whereHas('roles', function ($query) use ($allowedManagerLevel) {
                            $query->where('level', $allowedManagerLevel);
                        })->pluck('name', 'id');
                    })
                    ->nullable()
                    ->helperText('Assign a manager (only users with higher roles will appear).'),



                // Forms\Components\Repeater::make('issued_books')
                //     ->label('Issue Demo Books')
                //     ->relationship('issuedBooks')
                //     ->schema([
                //         Select::make('book_id')
                //             ->label('Book')
                //             ->options(\App\Models\Book::pluck('title', 'id')) // Fetch books dynamically
                //             ->required(),
                //         TextInput::make('count')
                //             ->label('Count')
                //             ->numeric()
                //             ->minValue(1)
                //             ->required()
                //             ->helperText('Enter the number of demo books issued.')
                //     ])
                //     ->createItemButtonLabel('Issue New Book')
                //     ->columns(2)
                //     ->collapsed(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

            // ->query(
            //     Invoice::query()
            //         ->whereHas('company', function (Builder $query) {
            //             $query->where('id', auth()->user()->company_id);
            //         })

            // )


            ->columns([
                TextColumn::make('name')
                    ->label('User Name')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->getStateUsing(fn($record) => $record->roles->pluck('name')->join(', ')),

                TextColumn::make('manager.name')
                    ->label('Manager')
                    ->getStateUsing(fn($record) => $record->manager ? $record->manager->name : 'No Manager'),
            ])

            ->filters([
                // Role filter
                // Tables\Filters\SelectFilter::make('role')
                //     ->label('Role')
                //     ->options(Role::pluck('name', 'id')->toArray())
                //     ->query(function ($query, $value) {
                //         return $query->whereHas('roles', fn($q) => $q->where('id', $value));
                //     }),




                SelectFilter::make('role')
                    ->label('Role')
                    ->options(
                        Role::pluck('name', 'id')->toArray()
                    )
                    ->query(function (Builder $query, array $data) {

                        if (!empty($data['value'])) {
                            $query->whereHas('roles', function (Builder $query) use ($data) {
                                $query->where('roles.id', $data['value']);
                            });
                        }
                    }),

                // Filter users with assigned managers
                Tables\Filters\Filter::make('has_manager')
                    ->label('Has Manager')
                    ->query(fn($query) => $query->whereNotNull('manager_id')),

                // Subordinates filter: Show subordinates for the current logged-in user
                Tables\Filters\Filter::make('subordinates')
                    ->label('My Subordinates')
                    ->query(function ($query) {
                        $user = auth()->user();

                        // Fetch all subordinate IDs for the logged-in user
                        $subordinateIds = $user->getAllSubordinateIds();

                        return $query->whereIn('id', $subordinateIds);
                    })
                    ->default(true) // Ensure it's applied by default
                    ->hidden(), // Prevent users from toggling or seeing the filter



            ])
            ->actions([
                Tables\Actions\Action::make('TopUp')
                    ->label('Top-Up Wallet')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->visible(fn(User $record) => auth()->user()->hasRole('accounts_head') )
                    ->modalHeading('Top-Up Wallet')
                    ->form([
                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->required()
                            ->rules(['min:1']) // Ensure minimum amount of 1
                            ->helperText('Enter the amount to top-up.'),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'cash' => 'Cash',
                                'bank_transfer' => 'Bank Transfer',
                                'credit_card' => 'Credit Card',
                            ])
                            ->required(),

                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->nullable()
                            ->helperText('Optional reference number for the payment.'),

                        DatePicker::make('payment_date')
                            ->required(),

                        FileUpload::make('payment_proof')
                            ->label('Payment Proof')
                            ->image()  // Specify that this is an image
                            ->directory('payment_proofs')  // Store the image in a specific directory
                            ->nullable(),
                    ])
                    ->action(function (array $data, User $record) {
                        // Handle file upload for payment proof
                        $paymentProofPath = null;
                        if (isset($data['payment_proof'])) {
                            $paymentProofPath = $data['payment_proof']->store('payment_proofs', 'public');
                        }

                        // Process the top-up
                        $amount = $data['amount'];

                        // Update the user's wallet balance
                        $record->wallet_balance += $amount;

                        $record->total_amount_given += $amount;

                        $record->amount_to_close += $amount;

                        $record->save();

                        // Log the wallet top-up transaction
                        WalletLog::create([
                            'user_id' => $record->id,
                            'company_id' => $record->company_id,
                            'amount' => $amount,
                            'payment_date' => $data['payment_date'],
                            'type' => 'credit',
                            'description' => 'Wallet top-up by admin',
                            'payment_method' => $data['payment_method'],
                            'reference_number' => $data['reference_number'],
                            'payment_proof' => $paymentProofPath,
                        ]);

                        // Send a database notification to the user
                        \Filament\Notifications\Notification::make()
                            ->title('Wallet Top-Up Successful')
                            ->body("Your wallet has been credited with an amount of $amount.")
                            ->success()
                            ->sendToDatabase($record);
                    })
                    ->requiresConfirmation()
            ]);
    }



    public static function getRelations(): array
    {
        return [
            // WalletLogsRelationManager::class,
            IssuedBooksRelationManager::class,
            SchoolCopyRelationManager::class,
            LeadStatusesRelationManager::class,
            DealClosedByRelationManager::class,


        ];
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
