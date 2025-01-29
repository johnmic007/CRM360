<?php

namespace App\Filament\Resources\VisitEntryResource\RelationManagers;

use App\Models\Block;
use App\Models\District;
use App\Models\SalesLeadManagement;
use App\Models\School;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class SchoolVisitRelationManager extends RelationManager
{
    protected static string $relationship = 'leadStatuses';



    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('state_id')
                    ->label('State')
                    ->options(function () {
                        $allocatedStates = auth()->user()->allocated_states ?? [];
                        return State::whereIn('id', $allocatedStates)
                            ->pluck('name', 'id');
                    })
                    ->reactive()
                    ->afterStateUpdated(fn(callable $set) => $set('district_id', null)),

                Forms\Components\Select::make('district_id')
                    ->label('District')
                    ->options(function () {
                        $allocatedDistricts = auth()->user()->allocated_districts ?? [];
                        return District::whereIn('id', $allocatedDistricts)
                            ->pluck('name', 'id');
                    })
                    ->reactive()
                    ->afterStateUpdated(fn(callable $set) => $set('block_id', null)),

                Forms\Components\Select::make('block_id')
                    ->label('Block')
                    ->options(function (callable $get) {
                        $districtId = $get('district_id');
                        if (!$districtId) {
                            return [];
                        }
                        return Block::where('district_id', $districtId)->pluck('name', 'id')->toArray();
                    })
                    ->reactive()
                    ->afterStateUpdated(fn(callable $set) => $set('school_id', null)),



                Forms\Components\Select::make('school_id')
                    ->label('School')
                    ->options(function (callable $get) {
                        $blockId = $get('block_id');
                        if (!$blockId) {
                            return [];
                        }
                        return School::where('block_id', $blockId)->pluck('name', 'id');
                    })
                    ->reactive()
                    ->searchable()
                    ->required()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if (!$state) {
                            return;
                        }

                        $assignedSchools = DB::table('school_user')->where('school_id', $state)->exists();

                        if (!$assignedSchools) {
                            $salesLeadManagement = SalesLeadManagement::firstOrCreate([
                                'school_id' => $state,
                                'district_id' => $get('district_id'),
                                'block_id' => $get('block_id'),
                                'state_id' => $get('state_id'),
                                'status' => 'School Nurturing',
                                'allocated_to' => auth()->id(),
                                'company_id' => auth()->user()->company_id ?? null,
                            ]);

                            DB::table('school_user')->insert([
                                'school_id' => $state,
                                'user_id' => auth()->id(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            // Pass the SalesLeadManagement ID to another field
                            $set('sales_lead_management_id', $salesLeadManagement->id);

                            $set('status', 'School Nurturing');
                        } else {
                            $status = SalesLeadManagement::where('school_id', $state)
                                ->where('allocated_to', auth()->id())
                                ->value('status');

                            $salesLeadManagementId = SalesLeadManagement::where('school_id', $state)
                                ->where('allocated_to', auth()->id())
                                ->value('id');

                            // Pass the existing SalesLeadManagement ID
                            $set('sales_lead_management_id', $salesLeadManagementId);

                            $set('status', $status ?? 'No Status Found');
                        }
                    }),

                Hidden::make('sales_lead_management_id')
                    ->label('Sales Lead Management ID'),




                Forms\Components\TextInput::make('status')
                    ->label(' Current status')
                    ->readOnly(),


                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(function (callable $get) {
                        $currentStatus = $get('status');

                        // dd($currentStatus);

                        // If no status is set yet, show all possible statuses
                        if (! $currentStatus) {
                            return [
                                'School Nurturing' => 'School Nurturing',
                                'Demo reschedule'  => 'Demo reschedule',
                                'Demo Completed'   => 'Demo Completed',
                                'deal_won'         => 'Deal Won',
                                'deal_lost'        => 'Deal Lost',
                            ];
                        }

                        // If current is School Nurturing
                        if ($currentStatus === 'School Nurturing') {
                            return [
                                'School Nurturing' => 'School Nurturing',
                                'Demo reschedule'  => 'Demo reschedule',
                            ];
                        }

                        // If current is Demo reschedule
                        if ($currentStatus === 'Demo reschedule') {
                            return [
                                'Demo Completed'  => 'Demo Completed',

                                'Demo reschedule' => 'Demo reschedule',
                            ];
                        }

                        // If current is Demo Completed
                        if ($currentStatus === 'Demo Completed') {
                            return [
                                'Demo Completed' => 'Demo Completed',

                            ];
                        }

                        // If current is deal_won
                        if ($currentStatus === 'deal_won') {
                            return [
                                'support'  => 'Support',
                            ];
                        }

                        // If current is deal_lost
                        if ($currentStatus === 'deal_lost') {
                            return [
                                'support'   => 'Support',
                            ];
                        }

                        if ($currentStatus === 'support') {
                            return [
                                'support'   => 'Support',
                            ];
                        }

                        // Default fallback (should rarely happen if the above cases cover everything)
                        return [
                            'School Nurturing' => 'School Nurturing',
                            'Demo reschedule'  => 'Demo reschedule',
                            'Demo Completed'   => 'Demo Completed',
                            'deal_won'         => 'Deal Won',
                            'support'   => 'Support',

                            'deal_lost'        => 'Deal Lost',
                        ];
                    })
                    ->reactive()
                    ->helperText('Specify the lead status.')
                    ->afterStateUpdated(function ($state, callable $get) {
                        // Automatically update SalesLeadManagement status
                        $salesLeadManagementId = $get('sales_lead_management_id');
                        if ($salesLeadManagementId) {
                            $salesLeadManagement = \App\Models\SalesLeadManagement::find($salesLeadManagementId);
                            if ($salesLeadManagement) {
                                $salesLeadManagement->update(['status' => $state]);
                            }
                        }
                    }),

                Forms\Components\Textarea::make('remarks')
                    ->label('Remarks')
                    ->required()
                    ->visible(fn(callable $get) => in_array($get('status'), ['School Nurturing', 'Demo reschedule' , 'support'])),

                Forms\Components\TextInput::make('contacted_person')
                    ->label('Contacted Person')
                    ->required()
                    ->visible(fn(callable $get) => $get('status') === ['School Nurturing', 'support']),

                Forms\Components\TextInput::make('contacted_person_designation')
                    ->label('Contacted Person Designation')
                    ->visible(fn(callable $get) => $get('status') === ['School Nurturing', 'support']),

                Forms\Components\Toggle::make('potential_meet')
                    ->label('Potential Meet')
                    ->visible(fn(callable $get) => in_array($get('status'), ['School Nurturing', 'Demo reschedule' , 'support' ])),

                Forms\Components\DatePicker::make('visited_date')
                    ->label('Visited Date')
                    ->default(now())
                    ->required()
                    ->visible(fn(callable $get) => in_array($get('status'), ['School Nurturing', 'Demo reschedule' , 'support' , 'deal_won' , 'deal_lost'])),

                Forms\Components\DatePicker::make('follow_up_date')
                    ->label('Follow-Up Date')
                    ->visible(fn(callable $get) => $get('status') === ['School Nurturing', 'Demo reschedule' , 'support' , 'deal_won' , 'deal_lost']),

                Forms\Components\DatePicker::make('reschedule_date')
                    ->label('Reschedule Date')
                    ->visible(fn(callable $get) => $get('status') === 'Demo reschedule'),

                Forms\Components\Radio::make('status')
                    ->label('Deal Status')
                    ->options([
                        'deal_won' => 'Deal Won',
                        'deal_lost' => 'Deal Lost',
                    ])
                    ->required()
                    ->helperText('Select whether the deal was won or lost.')
                    ->visible(fn(callable $get) => $get('status') === 'Demo Completed'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('school.name')->label('School'),
                Tables\Columns\TextColumn::make('block.name')->label('Block'),

                Tables\Columns\TextColumn::make('status')->label('Status'),
                Tables\Columns\TextColumn::make('visited_date')->label('Visited Date')->date(),
                Tables\Columns\BooleanColumn::make('potential_meet')->label('Potential Meet'),

            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add new visit')
                    ->visible(fn () => !$this->ownerRecord->end_time), // Hide the action if end_time is set
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }
}
