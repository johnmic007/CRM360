<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApprovalRequestResource\Pages;
use App\Models\ApprovalRequest;
use App\Models\School;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;

class ApprovalRequestResource extends Resource
{
    protected static ?string $model = ApprovalRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-clip';

    protected static ?string $navigationGroup = 'Approvals';

    protected static ?string $navigationLabel = 'lead Approvals request';

    protected static ?string $pluralLabel = 'lead Approvals request';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin' , 'bda' , 'bdm' , 'zonal_manager' , 'regional_manager' , 'head' , 'sales_operation']);
    }



    

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([

            Hidden::make('company_id')
            ->default(auth()->user()->company_id) // Automatically assign the user's company_id
            ->required(),
            Select::make('manager_id')
                ->label('Manager')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->disabled()
                ->required(),

            Select::make('user_id')
                ->label('Requested By')
                ->options(User::pluck('name', 'id'))
                ->searchable()
                ->disabled()
                ->required(),

            Select::make('school_id')
                ->label('School')
                ->options(School::pluck('name', 'id'))
                ->searchable()
                ->disabled()
                ->required(),

                Textarea::make('message')
                ->disabled(),

                Forms\Components\Select::make('status')
                ->label('Status')
                
                ->options([
                    'Pending' => 'Pending',
                    'Approved' => 'Approved',
                    'Rejected' => 'Rejected',
                ])
                ->disabled()
                ->required()
                // ->disabled(function () {
                //     $recordId = request()->route('record'); // Get the record ID from the route
                //     if (!$recordId) {
                //         return true; // Disable if no record ID is found
                //     }
            
                //     $record = ApprovalRequest::find($recordId); // Retrieve the record using the ID
                //     if (!$record) {
                //         return true; // Disable if the record does not exist
                //     }
            
                //     return auth()->id() !== $record->manager_id; // Disable if the logged-in user is not the manager
                // }),
            
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        // Get the authenticated user
        $user = auth()->user();
    
        return $table
            ->columns([
                TextColumn::make('manager.name')
                    ->label('Manager')
                    ->sortable()
                    ->searchable(),
    
                TextColumn::make('user.name')
                    ->label('Requested By')
                    ->sortable()
                    ->searchable(),
    
                TextColumn::make('school.name')
                    ->label('School')
                    ->sortable()
                    ->searchable(),
    
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'primary' => 'Pending',
                        'success' => 'Approved',
                        'danger' => 'Rejected',
                    ])
                    ->sortable(),
    
                TextColumn::make('created_at')
                    ->label('Requested At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters(array_filter([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ]),
    
                // Add manager_id filter only if user has the required roles
                $user && $user->hasRole(['admin', 'sales_operation', 'head'])
                    ? SelectFilter::make('manager_id')
                        ->label('Manager')
                        ->options(User::pluck('name', 'id')->toArray())
                    : null, // Exclude null values
            ]))
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }
    

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApprovalRequests::route('/'),
            'create' => Pages\CreateApprovalRequest::route('/create'),
            'edit' => Pages\EditApprovalRequest::route('/{record}/edit'),
        ];
    }
}
