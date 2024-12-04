<?php

namespace App\Filament\Pages;

use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use App\Models\SalesLeadManagement;
use App\Models\District;
use App\Models\Block;
use App\Models\School;
use App\Enums\SalesLeadStatus;
use Filament\Forms;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesLeadKanbanBoard extends KanbanBoard
{
    protected static string $model = SalesLeadManagement::class;

    protected static string $statusEnum = SalesLeadStatus::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';

    protected static string $recordTitleAttribute = 'school_id';

    protected static string $recordStatusAttribute = 'status';

    public function statuses(): \Illuminate\Support\Collection
    {
        return collect([
            ['id' => 'School Nurturing', 'title' => 'School Nurturing'],
            // ['id' => 'Lead Re-engaged', 'title' => 'Lead Re-engaged'],
            ['id' => 'Demo reschedule', 'title' => 'Demo schedule'],
            ['id' => 'Demo Completed', 'title' => 'Demo Completed'],
        ]);
    }




    protected function recordTitle($record): string
    {
        // Access the related school, user, and status
        $schoolName = $record->school->name ?? 'No School';
        $userName = $record->user->name ?? 'No User'; // Assuming the user is related to the record
        $status = $record->status ?? 'No Status';

        return "{$schoolName} | {$userName} | {$status}";
    }


    public function onStatusChanged(int $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
        SalesLeadManagement::find($recordId)->update(['status' => $status]);
    }

    public function onSortChanged(int $recordId, string $status, array $orderedIds): void
    {
        SalesLeadManagement::setNewOrder($orderedIds);
    }

    protected function getEditModalFormSchema(?int $recordId): array
    {
        $record = SalesLeadManagement::find($recordId);
        $fields = [
            Forms\Components\Fieldset::make('General Information')
                ->schema([
                    Forms\Components\Select::make('school_id')
                        ->label('School')
                        ->options(School::pluck('name', 'id'))
                        ->reactive()
                        ->required()
                        ->disabled()
                        ->helperText('Select the school for this lead.'),


                    // Forms\Components\Select::make('status')
                    //     ->label('Status')
                    //     ->options([
                    //         'Demo schedule' => 'Demo schedule',
                    //         'Demo reschedule' => 'Demo reschedule',
                    //     ])
                    //     ->required()
                    //     ->helperText('Specify the lead status.')

                ])
                ->columns(2),
        ];

        if ($record && in_array($record->status, ['Demo reschedule'])) {

            $fields = [
                Forms\Components\Fieldset::make('General Information')
                    ->schema([
                        Forms\Components\Select::make('school_id')
                            ->label('School')
                            ->options(School::pluck('name', 'id'))
                            ->reactive()
                            ->required()
                            ->disabled()
                            ->helperText('Select the school for this lead.'),


                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'Demo reschedule' => 'Demo schedule',
                                'Demo Completed' => 'Demo Completed',
                            ])
                            ->required()
                            ->helperText('Specify the lead status.')

                    ])
                    ->columns(2),
            ];
        }


        if ($record && in_array($record->status, ['School Nurturing', 'Lead Re-engaged'])) {

            $fields = [
                Forms\Components\Fieldset::make('General Information')
                    ->schema([
                        Forms\Components\Select::make('school_id')
                            ->label('School')
                            ->options(School::pluck('name', 'id'))
                            ->reactive()
                            ->required()
                            ->disabled()
                            ->helperText('Select the school for this lead.'),


                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'School Nurturing' => 'School Nurturing',
                                'Demo reschedule' => 'Demo schedule',
                            ])
                            ->required()
                            ->helperText('Specify the lead status.')

                    ])
                    ->columns(2),
            ];
        }


        if ($record && in_array($record->status, ['School Nurturing', 'Lead Re-engaged'])) {
            $fields[] = Forms\Components\Repeater::make('leadStatuses')
                ->relationship('leadStatuses', function ($query) {
                    $query->whereIn('status', ['School Nurturing', 'Lead Re-engaged']);
                })
                ->schema([
                    Forms\Components\Textarea::make('remarks')
                        ->label('Remarks')
                        ->rows(3)
                        ->placeholder('Add any relevant remarks...')
                        ->helperText('Provide additional notes or details about this status.'),
        
                    Forms\Components\TextInput::make('contacted_person')
                        ->label('Contacted Person')
                        ->placeholder('Enter the name of the contacted person.')
                        ->helperText('The name of the person contacted for this status.'),
        
                    Forms\Components\TextInput::make('contacted_person_designation')
                        ->label('Contacted Person Designation')
                        ->placeholder('e.g., Principal, Teacher')
                        ->helperText('Designation of the person contacted for this status.'),
        
                    Forms\Components\DatePicker::make('follow_up_date')
                        ->label('Follow-Up Date')
                        ->helperText('Specify the follow-up date for this status.'),
        
                    Forms\Components\DatePicker::make('visited_date')
                        ->label('Visited Date')
                        ->helperText('Specify the date when the school was visited.'),
                ])
                ->createItemButtonLabel('Add Follow-Up Entry')
                ->columns(2);
        }
        

        if ($record && in_array($record->status, ['Demo schedule', 'Demo reschedule'])) {
            $fields[] = Forms\Components\Repeater::make('leadStatuses')
            ->relationship('leadStatuses', function ($query) {
                $query->whereIn('status',  ['Demo schedule', 'Demo reschedule']);
            })                ->schema([
                    Forms\Components\Textarea::make('remarks')
                        ->label('Remarks')
                        ->rows(3)
                        ->placeholder('Add any relevant remarks...')
                        ->helperText('Provide additional notes or details about this status.'),

                    Forms\Components\DatePicker::make('visited_date')
                        ->label('Visited Date')
                        ->helperText('Specify the date when the school was visited.'),

                    Forms\Components\DatePicker::make('reschedule_date')
                        ->label(' Reschedule Date'),
                ])
                ->createItemButtonLabel('Add Demo schedule Entry')
                ->columns(2); // Display fields in two columns
        }


        if ($record && $record->status === 'Demo Completed') {
            $fields[] = Forms\Components\Fieldset::make('Deal Status')
                ->schema([
                    Forms\Components\Radio::make('status')
                        ->label('Deal Status')
                        ->options([
                            'deal_won' => 'Deal Won',
                            'deal_lost' => 'Deal Lost',
                        ])
                        ->required()
                        ->helperText('Select whether the deal was won or lost.'),
                ])
                ->columns(1); // Single column layout for clarity
        }

        return $fields;
    }

    protected function editRecord($recordId, array $data, array $state): void
    {
        $record = SalesLeadManagement::find($recordId);
        if ($record) {
            $record->update($data);
        }
    }

    /**
     * Define custom actions for the Kanban board.
     */
    protected function getActions(): array
    {
        return [
           

Action::make('addSchoolNurturing')
    ->label('Add to School Nurturing')
    ->icon('heroicon-o-plus-circle')
    ->modalHeading('Add New Sales Lead')
    ->form([
        Forms\Components\Select::make('district_id')
            ->label('District')
            ->options(District::pluck('name', 'id'))
            ->reactive()
            ->required(),

        Forms\Components\Select::make('block_id')
            ->label('Block')
            ->options(function (callable $get) {
                $districtId = $get('district_id');
                if (!$districtId) {
                    return [];
                }
                return Block::where('district_id', $districtId)->pluck('name', 'id');
            })
            ->reactive()
            ->required(),

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
            ->required(),
    ])
    ->action(function (array $data): void {
        // Check if the school is already assigned to a user
        $existingAssignment = DB::table('school_user')
            ->where('school_id', $data['school_id'])
            ->first();

        if ($existingAssignment) {
            
            $managerId = Auth::user()->manager_id;


            if ($managerId) {

                DB::table('approval_requests')->insert([
                    'manager_id' => $managerId,
                    'user_id' => Auth::id(),
                    'school_id' => $data['school_id'],
                    'status' => 'Pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                \Filament\Notifications\Notification::make()
                    ->title('Approval Request Sent')
                    ->success()
                    ->body('The school is already assigned to another user. Approval has been requested from their manager.')
                    ->send();

                return; // Exit the action since approval is needed
            } else {
                \Filament\Notifications\Notification::make()
                    ->title('Manager Not Found')
                    ->danger()
                    ->body('No manager is assigned to the current user. Please contact admin.')
                    ->send();

                return; // Exit the action
            }
        }

        // If no existing assignment, create the pivot entry
        DB::table('school_user')->insert([
            'school_id' => $data['school_id'],
            'user_id' => Auth::id(),
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        SalesLeadManagement::create(array_merge($data, ['status' => 'School Nurturing']));

        \Filament\Notifications\Notification::make()
            ->title('School Assigned')
            ->success()
            ->body('The school has been successfully assigned to you.')
            ->send();
    })

        ];
    }
}
