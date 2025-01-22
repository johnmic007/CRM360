<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadStageReportResource\Pages;
use App\Filament\Resources\LeadStageReportResource\RelationManagers;
use App\Filament\Resources\SchoolResource\RelationManagers\LeadStatusesRelationManager;
use App\Models\Block;
use App\Models\District;
use App\Models\LeadStageReport;
use App\Models\SalesLeadManagement;
use App\Models\SalesLeadStatus;
use App\Models\School;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LeadStageReportResource extends Resource
{
    protected static ?string $model = SalesLeadManagement::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationLabel = 'Lead Stage Report';

    protected static ?string $pluralLabel = 'Lead Stage Report';



    protected static ?string $navigationGroup = 'Reports';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'sales_operation' , 'sales_operation_head' , 'company' , 'head' , 'zonal_manager' , 'regional_manager' ]);
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

            TextInput::make('status')
                ->label('Status'),
              


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


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('school.name')->label('School')->searchable(),
                TextColumn::make('status')->label('Status'),
                TextColumn::make('total_visits')
                ->label('Total Visits')
                ->getStateUsing(function ($record) {
                    return $record->leadStatuses()->count();
                }),
                TextColumn::make('latest_visit_date')
                ->label('Latest Visit Date')
                ->getStateUsing(function ($record) {
                    return $record->leadStatuses()->max('visited_date');
                })
                ->date(),
                TextColumn::make('latest_follow_up_date')
                ->label('Latest Follow-Up Date')
                ->getStateUsing(function ($record) {
                    return $record->leadStatuses()->max('follow_up_date');
                })
                ->date(),

                
            TextColumn::make('latest_visited_by')
            ->label('Visited By (Latest)')
            ->getStateUsing(function ($record) {
                $latestVisit = $record->leadStatuses()->orderBy('visited_date', 'desc')->first();
                return $latestVisit && $latestVisit->visitedBy ? $latestVisit->visitedBy->name : 'Unknown';
            }),


            ])
            ->filters([

                Tables\Filters\SelectFilter::make('status')
                ->label('Status')
                ->options([
                    'School Nurturing' => 'School Nurturing',
                    'Demo Completed' => 'Demo Completed',
                    'Demo reschedule' => 'Demo Schedule',
                    'deal_won' => 'Deal Won',
                    'deal_lost' => 'Deal lost',
                ]),
                // Filter by Visited Date
                Tables\Filters\Filter::make('visited_date')
                ->form([
                    DatePicker::make('date')
                        ->label('visited_date'),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query->whereHas('leadStatuses', function ($q) use ($data) {
                        $q->when(
                            $data['date'],
                            fn($query, $date) => $query->whereDate('visited_date', $date)
                        );
                    });
                })
                ->label('Visited Date')
                ->indicateUsing(function (array $data) {
                    return !empty($data['date']) ? 'Visited Date: ' . $data['date'] : null;
                }),
            
            Tables\Filters\Filter::make('follow_up_date')
                ->form([
                    DatePicker::make('date')
                        ->label('Follow-Up Date'),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query->whereHas('leadStatuses', function ($q) use ($data) {
                        $q->when(
                            $data['date'],
                            fn($query, $date) => $query->whereDate('follow_up_date', $date)
                        );
                    });
                })
                ->label('Follow-Up Date')
                ->indicateUsing(function (array $data) {
                    return !empty($data['date']) ? 'Follow-Up Date: ' . $data['date'] : null;
                }),
            
            // Filter by Latest Visited By
            Tables\Filters\Filter::make('visited_by')
                ->label('Visited By')
                ->form([
                    Select::make('user_id')
                        ->label('User')
                        ->options(
                            \App\Models\User::role(['BDA', 'BDM']) // Fetch only BDA and BDM users
                                ->pluck('name', 'id')
                                ->toArray()
                        )
                        ->searchable(),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query->whereHas('leadStatuses', function ($q) use ($data) {
                        if (!empty($data['user_id'])) {
                            $q->where('visited_by', $data['user_id']);
                        }
                    });
                })
                ->indicateUsing(function (array $data) {
                    if (!empty($data['user_id'])) {
                        $user = \App\Models\User::find($data['user_id']);
                        return $user ? 'Visited By: ' . $user->name : null;
                    }
                    return null;
                }),
            
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
           
    }

    public static function getRelations(): array
    {
        return [

            LeadStatusesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeadStageReports::route('/'),
            'create' => Pages\CreateLeadStageReport::route('/create'),
            'view' => Pages\ViewLeadStageReport::route('/{record}'),
            'edit' => Pages\EditLeadStageReport::route('/{record}/edit'),
        ];
    }
}
