<?php

namespace App\Filament\Resources\CreateExpnessResource\RelationManagers;

use App\Models\Block;
use App\Models\District;
use App\Models\School;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\SalesLeadManagement;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class CreateLeadStatusesRelationManager extends RelationManager
{
    protected static string $relationship = 'leadStatuses';


    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('state_id')
                ->label('State')
                ->options(function () {
                    $visitEntry = $this->getOwnerRecord(); // Retrieve the owner record (VisitEntry)
            
                    // Retrieve the related User model
                    $user = $visitEntry?->user; // Assuming VisitEntry has a `user` relationship
            
                    if (!$user) {
                        return [];
                    }
            
                    $allocatedStates = $user->allocated_states ?? [];
            
                    return State::whereIn('id', $allocatedStates)->pluck('name', 'id');
                })
                ->reactive()
                ->afterStateUpdated(fn(callable $set) => $set('district_id', null)),
            
            Forms\Components\Select::make('district_id')
                ->label('District')
                ->options(function (callable $get) {
                    $stateId = $get('state_id'); // Get the selected state
                    if (!$stateId) {
                        return [];
                    }
            
                    $visitEntry = $this->getOwnerRecord(); // Retrieve the owner record (VisitEntry)
                    if (!$visitEntry) {
                        return [];
                    }
            
                    // Retrieve districts allocated to the user associated with the VisitEntry
                    $user = $visitEntry->user;
                    if (!$user) {
                        return [];
                    }
            
                    $allocatedDistricts = $user->allocated_districts ?? [];
                    return District::where('state_id', $stateId)
                        ->whereIn('id', $allocatedDistricts)
                        ->pluck('name', 'id');
                })
                ->reactive()
                ->afterStateUpdated(fn(callable $set) => $set('block_id', null)),
            
            Forms\Components\Select::make('block_id')
                ->label('Block')
                ->options(function (callable $get) {
                    $districtId = $get('district_id'); // Get the selected district
                    if (!$districtId) {
                        return [];
                    }
            
                    return Block::where('district_id', $districtId)->pluck('name', 'id');
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

                        $currentUserId = auth()->id();


                        $assignedSchools = DB::table('school_user')->where('school_id', $state)->exists();

                        $visitEntry = $this->getOwnerRecord(); // Retrieve the owner record (VisitEntry)
            
                        // Retrieve the related User model
                        $user = $visitEntry?->user_id;

                        
                        if (!$assignedSchools) {
                            $salesLeadManagement = SalesLeadManagement::firstOrCreate([
                                'school_id' => $state,
                                'district_id' => $get('district_id'),
                                'block_id' => $get('block_id'),
                                'state_id' => $get('state_id'),
                                'status' => 'School Nurturing',
                                'allocated_to' => $user,
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
                                ->where('allocated_to', $user)
                                ->value('status');

                            $salesLeadManagementId = SalesLeadManagement::where('school_id', $state)
                                ->where('allocated_to',$user)
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
                    ->visible(fn(callable $get) => in_array($get('status'), ['School Nurturing', 'Demo reschedule', 'support'])),


                FileUpload::make('image')
                    ->label('images')
                    ->required()

                    ->helperText('Upload image')
                    ->visible(fn(callable $get) => in_array($get('status'), ['School Nurturing', 'Demo reschedule', 'Demo Completed', 'support', 'deal_won', 'deal_lost'])),

                Forms\Components\TextInput::make('contacted_person_designation')
                    ->label('Contacted Person Designation')
                    ->placeholder('e.g., Principal, Teacher')
                    ->required()
                    ->helperText('Designation of the person contacted for this status.')
                    ->visible(fn(callable $get) => in_array($get('status'), ['School Nurturing', 'Demo reschedule', 'Demo Completed', 'support', 'deal_won', 'deal_lost'])),



                Forms\Components\TextInput::make('contacted_person')
                    ->label('Contacted Person')
                    ->required()
                    ->visible(fn(callable $get) => in_array($get('status'), ['School Nurturing', 'Demo reschedule', 'Demo Completed', 'support', 'deal_won', 'deal_lost'])),


                Forms\Components\DatePicker::make('follow_up_date')
                    ->label('Follow-Up Date')
                    ->helperText('Specify the follow-up date for this status.')

                    ->visible(fn(callable $get) => in_array($get('status'), ['School Nurturing', 'Demo reschedule', 'Demo Completed', 'support', 'deal_won', 'deal_lost'])),


                Forms\Components\TextInput::make('contacted_person_designation')
                    ->label('Contacted Person Designation')
                    ->visible(fn(callable $get) => $get('status') === ['School Nurturing', 'support']),



                Forms\Components\DatePicker::make('visited_date')
                    ->label('Visited Date')
                    ->default(now())
                    ->required()
                    ->visible(fn(callable $get) => in_array($get('status'), ['School Nurturing', 'Demo reschedule', 'Demo Completed', 'support', 'deal_won', 'deal_lost'])),


                Forms\Components\Toggle::make('potential_meet')
                    ->label('Potential Meet')
                    ->visible(fn(callable $get) => in_array($get('status'), ['School Nurturing', 'Demo reschedule', 'support'])),

                Forms\Components\DatePicker::make('follow_up_date')
                    ->label('Follow-Up Date')
                    ->visible(fn(callable $get) => in_array($get('status'), ['School Nurturing', 'Demo reschedule', 'Demo Completed', 'support', 'deal_won', 'deal_lost'])),

                Forms\Components\DatePicker::make('reschedule_date')
                    ->label('Reschedule Date')
                    ->visible(fn(callable $get) => $get('status') === 'Demo reschedule'),

                Forms\Components\Radio::make('status')
                    ->label('Deal Status')
                    ->options([
                        'deal_won' => 'Deal Won',
                        'deal_lost' => 'Deal Lost',
                    ])
                    ->helperText('Select whether the deal was won or lost.')
                    ->visible(fn(callable $get) => $get('status') === 'Demo Completed'),


                Forms\Components\Toggle::make('is_book_issued')
                    ->label('Was a book issued during this visit?')
                    ->reactive()
                    ->helperText('Check if demo books were provided to the school.'),


                Forms\Components\Repeater::make('issued_books_log')
                    ->label('Books Issued/Returned')
                    ->relationship('issuedBooksLog')
                    ->schema([


                        Forms\Components\Hidden::make('created_by')
                        ->default(fn($get) => $this->getOwnerRecord()?->user_id) // Retrieve `user_id` from the parent record
                        ->required(),


                        Forms\Components\Hidden::make('school_id')
                            ->label('scl')
                            ->default(fn($get) => $get('../school_id')), // Fetch school_id dynamically from parent

                        Select::make('book_id')
                            ->label('Book')
                            ->options(\App\Models\Book::pluck('title', 'id'))
                            ->required(),
                        Select::make('action')
                            ->label('Action')
                            ->options([
                                'issued' => 'Issued',
                                'returned' => 'Returned',
                            ])
                            ->required(),
                        TextInput::make('count')
                            ->label('Count')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->helperText('Enter the number of books issued or returned.'),
                    ])
                    ->columns(2)
                    // ->disableItemEditing() // This disables editing of items
                    ->disableItemDeletion()
                    ->visible(fn($get) => $get('is_book_issued')),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('school.name')->label('School'),
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

            ]);
    }
}
