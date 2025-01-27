<?php

namespace App\Filament\Resources\VisitEntryResource\RelationManagers;

use App\Models\Block;
use App\Models\District;
use App\Models\SalesLeadManagement;
use App\Models\School;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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



                // Forms\Components\Select::make('school_id')
                //     ->label('School')
                //     ->options(function (callable $get) {
                //         $blockId = $get('block_id');
                //         if (!$blockId) {
                //             return [];
                //         }
                //         return School::where('block_id', $blockId)->pluck('name', 'id');
                //     })
                //     ->reactive()
                //     ->searchable()
                //     ->required()
                //     ->afterStateUpdated(function ($state, callable $set, callable $get) {
                //         if (!$state) {
                //             return;
                //         }

                //         $currentUserId = auth()->id();


                //         $assignedSchools = DB::table('school_user')->where('school_id', $state)->exists();

                //         if (!$assignedSchools) {
                //             $salesLeadManagement = SalesLeadManagement::firstOrCreate([
                //                 'school_id' => $state,
                //                 'district_id' => $get('district_id'),
                //                 'block_id' => $get('block_id'),
                //                 'state_id' => $get('state_id'),
                //                 'status' => 'School Nurturing',
                //                 'allocated_to' => auth()->id(),
                //                 'company_id' => auth()->user()->company_id ?? null,
                //             ]);

                //             DB::table('school_user')->insert([
                //                 'school_id' => $state,
                //                 'user_id' => auth()->id(),
                //                 'created_at' => now(),
                //                 'updated_at' => now(),
                //             ]);

                //             // Pass the SalesLeadManagement ID to another field
                //             $set('sales_lead_management_id', $salesLeadManagement->id);

                //             $set('status', 'School Nurturing');
                //         } else {
                //             $status = SalesLeadManagement::where('school_id', $state)
                //                 ->where('allocated_to', auth()->id())
                //                 ->value('status');

                //             $salesLeadManagementId = SalesLeadManagement::where('school_id', $state)
                //                 ->where('allocated_to', auth()->id())
                //                 ->value('id');

                //             // Pass the existing SalesLeadManagement ID
                //             $set('sales_lead_management_id', $salesLeadManagementId);

                //             $set('status', $status ?? 'No Status Found');
                //         }
                //     }),


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

                        // Check if the school is already assigned in `school_user`
                        $assignedSchools = DB::table('school_user')->where('school_id', $state)->exists();

                        if ($assignedSchools) {
                            // Check if the current user is assigned to this school
                            $isCurrentUserAssigned = DB::table('school_user')
                                ->where('school_id', $state)
                                ->where('user_id', $currentUserId)
                                ->exists();


                            if (!$isCurrentUserAssigned) {
                                // If the school is assigned to another user
                                // dd($isCurrentUserAssigned);

                                // dd('kjdhsuj');
                                $set('status', 'Assigned to Another User');

                                // Update the message in another input box
                                $set('warning_message', 'This school is assigned to another user. Please request your manager to assign this school to you.');
                                return;
                            }

                            // If the current user is assigned, fetch the existing status
                            $status = SalesLeadManagement::where('school_id', $state)
                                ->where('allocated_to', auth()->id())
                                ->value('status');

                            $salesLeadManagementId = SalesLeadManagement::where('school_id', $state)
                                ->where('allocated_to', auth()->id())
                                ->value('id');

                            // Pass the existing SalesLeadManagement ID
                            $set('sales_lead_management_id', $salesLeadManagementId);

                            $set('status', $status ?? 'No Status Found');

                            // Clear any previous warning message
                            $set('warning_message', null);
                        } else {
                            // If the school is not yet assigned, mark it for the current user
                            $set('status', 'School Nurturing');
                            $set('warning_message', null);
                        }
                    }),

                Forms\Components\Section::make()
                    ->schema([
                        // Styled Warning Message
                        Forms\Components\Textarea::make('warning_message')
                            ->label('⚠️ Warning Message')
                            ->placeholder('No warnings at the moment.')
                            ->rows(3)
                            ->readOnly()
                            ->reactive()
                            ->extraAttributes([
                                'style' => '
                                color: var(--warning-text-color);
                                background-color: var(--warning-bg-color);
                                font-weight: bold;
                                border: 1px solid var(--warning-border-color);
                                padding: 10px;
                                border-radius: 5px;
                            '
                            ])
                            ->helperText('This section will display warnings, if applicable.'),

                        // Styled Input for Manager Request
                        Forms\Components\Textarea::make('message')
                            ->label('📝 Request Message')
                            ->placeholder('Write your message to request reassignment from your manager.')
                            ->rows(3)
                            ->required()
                            ->helperText('Compose a message to send to your manager. Be clear and concise.')
                            ->extraAttributes([
                                'style' => '
                                border: 1px solid var(--input-border-color);
                                color: var(--text-color);
                                background-color: var(--input-bg-color);
                                padding: 10px;
                                border-radius: 5px;
                            '
                            ]),
                    ])
                    ->extraAttributes([
                        'style' => '
                        background-color: var(--section-bg-color);
                        border: 1px solid var(--section-border-color);
                        padding: 15px;
                        border-radius: 10px;
                    '
                    ])
                    ->hidden(fn(callable $get) => !$get('warning_message')), // Hide the section if there's no warning

                // Forms\Components\Select::make('school_id')
                //     ->label('School')
                //     ->options(function (callable $get) {
                //         $blockId = $get('block_id');
                //         if (!$blockId) {
                //             return [];
                //         }
                //         return School::where('block_id', $blockId)->pluck('name', 'id');
                //     })
                //     ->reactive()
                //     ->searchable()
                //     ->required()
                //     ->afterStateUpdated(function ($schoolId, callable $set, callable $get) {
                //         if (! $schoolId) {
                //             return;
                //         }

                //         // Try to find existing SalesLeadManagement for this user & school
                //         $salesLeadManagement = \App\Models\SalesLeadManagement::where('school_id', $schoolId)
                //             ->where('allocated_to', auth()->id())
                //             ->first();

                //         if ($salesLeadManagement) {
                //             // Pass the existing SalesLeadManagement ID
                //             $set('sales_lead_management_id', $salesLeadManagement->id);

                //             // Set the current status from that record
                //             $set('status', $salesLeadManagement->status ?? 'No Status Found');
                //         } else {
                //             // If none found, reset these fields for now
                //             $set('sales_lead_management_id', null);
                //             $set('status', 'No Status Found');
                //         }
                //     }),

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
                    ->hidden(fn(callable $get) => $get('warning_message')) // Hide the section if there's no warning

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
                    ->visible(fn(callable $get) => in_array($get('status'), ['School Nurturing', 'Demo reschedule', 'Demo Completed', 'support', 'deal_won', 'deal_lost'])),


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



                    Forms\Components\Checkbox::make('skip_contact')
                    ->reactive()
                    ->label('Do not collect contact number'),

                    Forms\Components\TextInput::make('contact_number')
                    ->label('Contact Number')
                    ->numeric()
                    ->reactive()
                    ->minLength(10) // Minimum length of 10 digits
                    ->maxLength(12)
                    ->placeholder('Enter a 10-12 digit contact number') // Placeholder for guidance

                    ->required(fn(callable $get) => !$get('skip_contact')) // Required only if 'skip_contact' is unchecked
                    ->visible(fn(callable $get) => !$get('skip_contact')), // Hidden if 'skip_contact' is checked

                
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
                    ->visible(fn(callable $get) => in_array($get('status'), ['School Nurturing', 'Demo reschedule', 'Demo Completed', 'support', 'deal_won', 'deal_lost'])),

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
                    ->hidden(fn(callable $get) => $get('warning_message')) // Hide the section if there's no warning

                    ->label('Was a book issued during this visit?')
                    ->reactive()
                    ->helperText('Check if demo books were provided to the school.'),


                Forms\Components\Repeater::make('issued_books_log')
                    ->label('Books Issued/Returned')
                    ->relationship('issuedBooksLog')
                    ->schema([

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
                Tables\Columns\TextColumn::make('block.name')->label('Block'),
                Tables\Columns\TextColumn::make('contact_number'),


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
                    ->visible(fn() => !$this->ownerRecord->end_time), // Hide the action if end_time is set
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

            ]);
    }
}
