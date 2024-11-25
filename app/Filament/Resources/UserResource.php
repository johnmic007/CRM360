<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Role;
use Filament\Forms;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                    ->unique(User::class, 'email'),

                // Password input
                TextInput::make('password')
                    ->password()
                    ->required()
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table

        ->query(
            Invoice::query()
                ->whereHas('company', function (Builder $query) {
                    $query->where('id', auth()->user()->company_id);
                })
              
        )

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
                    }),
            ]);
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
