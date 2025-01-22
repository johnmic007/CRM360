<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SchoolReportResource\Pages;
use App\Filament\Resources\SchoolReportResource\RelationManagers;
use App\Models\Block;
use App\Models\District;
use App\Models\SalesLeadManagement;
use App\Models\SalesLeadStatus;
use App\Models\School;
use App\Models\SchoolReport;
use App\Models\State;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SchoolReportResource extends Resource
{
    protected static ?string $model = SalesLeadStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = 'School Report';

    protected static ?string $pluralLabel = 'School Report';



    protected static ?string $navigationGroup = 'Reports';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'sales_operation' , 'company' , 'sales_operation_head' , 'head' , 'zonal_manager' , 'regional_manager' ]);
    }



    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                // Forms\Components\Select::make('sales_lead_management_id')
                //     ->label('Sales Lead Management')
                //     ->options(SalesLeadManagement::pluck('school.name', 'id'))
                //     ->searchable()
                //     ->required(),

                Forms\Components\Select::make('school_id')
                    ->label('School')
                    ->options(School::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('state_id')
                    ->label('State')
                    ->options(State::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('district_id')
                    ->label('District')
                    ->options(District::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('block_id')
                    ->label('Block')
                    ->options(Block::pluck('name', 'id'))
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('status')
                    ->label('Status')
                    ->required(),


                Forms\Components\TextInput::make('contacted_person')
                    ->label('Contacted Person')
                    ->required(),

                Forms\Components\TextInput::make('contacted_person_designation')
                    ->label('Designation')
                    ->required(),

                Forms\Components\Textarea::make('remarks')
                    ->label('Remarks'),

                Forms\Components\FileUpload::make('image')
                    ->label('Image'),

                Forms\Components\DatePicker::make('visited_date')
                    ->label('Visited Date')
                    ->required(),

                Forms\Components\DatePicker::make('follow_up_date')
                    ->label('Follow-Up Date'),

                Forms\Components\DatePicker::make('reschedule_date')
                    ->label('Reschedule Date'),

                Forms\Components\Toggle::make('is_book_issued')
                    ->label('Book Issued'),
            ]);
    }


    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('school.name')
                    ->label('School')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('visitedBy.name')
                    ->label('Visited By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BooleanColumn::make('potential_meet')
                    ->label('Potential Meet')
                    ->getStateUsing(fn($record) => $record->potential_meet > 0) // True if potential meet > 0
                    ->trueIcon('heroicon-o-check-circle') // Icon for true (tick)
                    ->falseIcon('heroicon-o-x-circle')    // Icon for false (cross)
                    ->trueColor('success') // Green color for tick
                    ->falseColor('danger'), // Red color for cross


               

                // Tables\Columns\BooleanColumn::make('is_book_issued')
                //     ->label('Book Issued'),

                


                TextColumn::make('visited_date')
                    ->label('Visited Date'),

                TextColumn::make('follow_up_date')
                    ->label('Follow-Up Date'),

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
                ])
                ->attribute('status') // Specify the attribute to filter
                ->searchable()
                ->query(function (Builder $query, array $data) {
                    if (!empty($data['value'])) {
                        $query->where('status', $data['value']);
                    }
                })
                ->indicateUsing(function (array $data) {
                    return !empty($data['value']) ? 'Status: ' . ucfirst($data['value']) : null;
                }),
                // Tables\Filters\SelectFilter::make('state_id')
                //     ->label('State')
                //     ->options(State::pluck('name', 'id')->toArray()),

                // Tables\Filters\SelectFilter::make('district_id')
                //     ->label('District')
                //     ->options(District::pluck('name', 'id')->toArray()),

                // Tables\Filters\SelectFilter::make('block_id')
                //     ->label('Block')
                //     ->options(Block::pluck('name', 'id')->toArray()),

                // Tables\Filters\SelectFilter::make('school_id')
                //     ->label('School')
                //     ->options(School::pluck('name', 'id')->toArray()),

                Tables\Filters\Filter::make('potential_meet')
                ->label('Potential Meet')
                ->query(fn (Builder $query) => $query->where('potential_meet', '>', 0)),

                Tables\Filters\Filter::make('visited_date')
                ->label('Visited Date')
                ->form([
                    Forms\Components\DatePicker::make('date')
                        ->label('visited_date'),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query->when($data['date'], fn($q) => $q->whereDate('visited_date', $data['date']));
                })
                ->indicateUsing(function (array $data) {
                    return !empty($data['date']) ? 'Visited Date: ' . $data['date'] : null;
                }),



                Tables\Filters\Filter::make('follow_up_date')
                ->label('Follow-Up Date')
                ->form([
                    Forms\Components\DatePicker::make('date')
                        ->label('Follow-Up Date'),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query->when($data['date'], fn($q) => $q->whereDate('follow_up_date', $data['date']));
                })
                ->indicateUsing(function (array $data) {
                    return !empty($data['date']) ? 'Follow-Up Date: ' . $data['date'] : null;
                }),

            // User-wise Filter for BDA and BDM
            Tables\Filters\Filter::make('visited_by')
            ->label('Visited By Role')
            ->form([
                Forms\Components\Select::make('user_id')
                    ->label('Visited By')
                    ->options(
                        User::role(['BDA', 'BDM']) // Retrieves users with these roles
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->searchable(),
            ])
            ->query(function (Builder $query, array $data) {
                if (!empty($data['user_id'])) {
                    $query->where('visited_by', $data['user_id']); // Adjust the column name as per your database schema
                }
            })
            ->indicateUsing(function (array $data) {
                if (!empty($data['user_id'])) {
                    $user = User::find($data['user_id']);
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolReports::route('/'),
            'create' => Pages\CreateSchoolReport::route('/create'),
            'view' => Pages\ViewSchoolReport::route('/{record}'),
            'edit' => Pages\EditSchoolReport::route('/{record}/edit'),
        ];
    }
}
