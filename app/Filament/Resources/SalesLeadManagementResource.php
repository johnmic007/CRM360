<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesLeadManagementResource\Pages;
use App\Filament\Resources\SchoolResource\RelationManagers\LeadStatusesRelationManager;
use App\Models\Block;
use App\Models\District;
use App\Models\SalesLeadManagement;
use App\Models\School;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\DB;

class SalesLeadManagementResource extends Resource
{
    protected static ?string $model = SalesLeadManagement::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'bda', 'bdm', 'zonal_manager', 'regional_manager', 'head', 'sales_operation']);
    }


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('district_id')
                ->label('District')
                ->options(District::pluck('name', 'id')->toArray()) // Fetch districts using Eloquent
                ->reactive()
                ->required()
                ->disabled(fn($record) => $record !== null), // Disable if a record is being edited


            Forms\Components\Select::make('block_id')
                ->label('Block')
                ->options(function (callable $get) {
                    $districtId = $get('district_id');
                    if (!$districtId) {
                        return [];
                    }
                    return Block::where('district_id', $districtId)->pluck('name', 'id')->toArray(); // Fetch blocks using Eloquent
                })
                ->reactive()
                ->required()
                ->disabled(fn($record) => $record !== null), // Disable if a record is being edited


            Forms\Components\Select::make('school_id')
                ->label('School')
                ->options(function (callable $get) {
                    $blockId = $get('block_id');
                    if (!$blockId) {
                        return [];
                    }
                    return School::where('block_id', $blockId)->pluck('name', 'id')->toArray(); // Fetch schools using Eloquent
                })
                ->reactive()
                ->disabled(fn($record) => $record !== null) // Disable if a record is being edited
                ->required(),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'School Nurturing' => 'School Nurturing',
                    'active' => 'Active',
                    'rejected' => 'Rejected',
                    'converted' => 'Converted',
                ])
                ->reactive()
                ->disabled()
                ->default('School Nurturing') // Set the default value
                ->required(),

            Forms\Components\Select::make('allocated_to')
                ->label('Allocated To')
                ->options(function () {
                    $user = auth()->user();

                    // Admins can see all users
                    if ($user->hasRole('admin')) {
                        return \App\Models\User::pluck('name', 'id')->toArray();
                    }

                    // Fetch IDs of the current user and their subordinates
                    $accessibleUserIds = $user->getAllSubordinateIds();
                    $accessibleUserIds[] = $user->id; // Include the user's own ID

                    return \App\Models\User::whereIn('id', $accessibleUserIds)->pluck('name', 'id')->toArray();
                })
                // ->hidden(fn() => !auth()->user()->hasRole('admin')) // Disable for non-admins
                ->default(auth()->id()) // Set default to current user's ID
                ->required()
                ->disabled(fn($record) => $record !== null), // Disable if a record is being edited


            Forms\Components\Textarea::make('feedback')
                ->label('Feedback')
                ->placeholder('Provide feedback here...')
                ->visible(fn(callable $get) => in_array($get('status'), ['active', 'rejected']))
                ->required(fn(callable $get) => in_array($get('status'), ['active', 'rejected'])),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            // Tables\Columns\TextColumn::make('district.name')->label('District'),
            // Tables\Columns\TextColumn::make('block.name')->label('Block'),
            Tables\Columns\TextColumn::make('school.name')->label('School'),
            Tables\Columns\TextColumn::make('allocatedUser.name')->label('Allocated user'),
            Tables\Columns\TextColumn::make('allocatedUser.roles.name')
                ->label('Role')
                ->getStateUsing(fn($record) => $record->allocatedUser?->roles->pluck('name')->join(', ') ?? 'No Role'),

            Tables\Columns\TextColumn::make('status')->label('Status'),
            // Tables\Columns\TextColumn::make('feedback')->label('Feedback')->limit(50),
        ])
            ->filters([

                //    Tables\Filters\SelectFilter::make('status')
                //     ->label('Status')
                //     ->options([
                //         'School Nurturing' => 'School Nurturing',
                //         'active' => 'Active',
                //         'rejected' => 'Rejected',
                //         'converted' => 'Converted',
                //     ])
                //     ->query(function ($query, $value) {
                //         $query->where('status', $value);
                //     }),

                // Filter by Allocated User
                // Tables\Filters\SelectFilter::make('allocated_to')
                //     ->label('Allocated User')
                //     ->options(\App\Models\User::pluck('name', 'id')->toArray())
                //     ->query(function ($query, $value) {
                //         $query->where('allocated_to', $value);
                //     }),

                // Filter by District
                SelectFilter::make('district_id')
                    ->label('District')
                    ->options(\App\Models\District::pluck('name', 'id')->toArray()),



                    // SelectFilter::make('school_id')
                    // ->label('School')
                    // ->relationship('school', 'name')


            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            // \App\Filament\Resources\SchoolResource\RelationManagers\InvoicesRelationManager::class,
            // \App\Filament\Resources\SchoolResource\RelationManagers\BookRelationManager::class,
            LeadStatusesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesLeadManagement::route('/'),
            'create' => Pages\CreateSalesLeadManagement::route('/create'),
            'edit' => Pages\EditSalesLeadManagement::route('/{record}/edit'),
        ];
    }
}
