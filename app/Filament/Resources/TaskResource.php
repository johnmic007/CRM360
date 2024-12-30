<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Block;
use App\Models\District;
use App\Models\School;
use App\Models\Task;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;

use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';



    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['trainer' , 'admin' , 'head_trainer' ]);
    } 

    public static function canCreate(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'sales_operation' , 'head_trainer']);
    }


    public static function form(Form $form): Form
    {

        $user = auth()->user();

        return $form
            ->schema([

                Forms\Components\Hidden::make('company_id')
                    ->default(fn() => auth()->user()->company_id) // Set the default to the user's company_id

                    ->required(),

                Select::make('status')
                    ->label('Task Status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                    ])
                    ->default('pending')
                    ->disabled($user->hasAnyRole(['admin', 'sales_operation' , 'head_trainer']))
                    ->reactive()
                    ->live()
                    ->extraAttributes(function (callable $get) use ($user) {
                        $statusColors = [
                            'pending' => 'background-color: #ffeb3b; color: #000;', // Yellow for Pending
                            'in_progress' => 'background-color: #2196f3; color: #fff;', // Blue for In Progress
                            'completed' => 'background-color: #4caf50; color: #fff;', // Green for Completed
                            'cancelled' => 'background-color: #f44336; color: #fff;', // Red for Cancelled
                        ];

                        $status = $get('status') ?? 'pending'; // Get the current status dynamically
                        $baseStyle = $statusColors[$status] ?? 'background-color: #f8f9fa; color: #000;';

                        if ($user->hasAnyRole(['admin', 'sales_operation'])) {
                            $baseStyle .= ' border: 2px solid #4CAF50; font-weight: bold;';
                        }

                        return ['style' => $baseStyle];
                    }),




                Section::make('Additional Information')
                    ->description('Provide details about the session')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('total_students')
                                    ->label('Total Students Attended')
                                    ->numeric()
                                    ->disabled($user->hasAnyRole(['admin', 'sales_operation', 'head_trainer']))
                                    ->placeholder('Enter the number of students')
                                    ->required(),

                                Textarea::make('topics_covered')
                                    ->label('Topics Covered')
                                    ->disabled($user->hasAnyRole(['admin', 'sales_operation', 'head_trainer']))
                                    ->placeholder('List the topics covered during the session')
                                    ->rows(4)
                                    ->required(),

                                Textarea::make('remarks')
                                    ->label('Remarks')
                                    ->disabled($user->hasAnyRole(['admin', 'sales_operation', 'head_trainer']))
                                    ->placeholder('Add any additional remarks')
                                    ->rows(3)
                                    ->helperText('Optional: Add notes or feedback for this session.'),

                                FileUpload::make('image')
                                    ->label('Upload Image')
                                    ->disabled($user->hasAnyRole(['admin', 'sales_operation' , 'head_trainer']))
                                    ->image()
                                    ->directory('task-images')
                                    ->helperText('Optional: Upload an image related to this task.'),
                            ]),
                    ])
                    ->collapsible()
                    ->hidden(fn ($get) => $get('status') === 'pending'),





                Section::make('Task Overview')
                    ->description('Provide the basic details of the task')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Task Title')
                                    ->placeholder('Enter the task title (e.g., Demo Presentation)')
                                    ->required()
                                    ->disabled(!$user->hasAnyRole(['admin', 'sales_operation' , 'head_trainer']))
                                    ->columnSpan(2)
                                    ->helperText('This is the primary title of the task.'),

                                Textarea::make('description')
                                    ->label('Task Description')
                                    ->placeholder('Provide a detailed description about the task...')
                                    ->rows(4)
                                    ->disabled(!$user->hasAnyRole(['admin', 'sales_operation' , 'head_trainer']))
                                    ->helperText('Optional: Add any additional notes or requirements.'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Task Schedule')
                    ->description('Set the schedule for this task')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Start Date')
                                    ->required()
                                    ->disabled(!$user->hasAnyRole(['admin', 'sales_operation' , 'head_trainer' ]))
                                    ->helperText('Select the date when the task starts.'),

                                DatePicker::make('end_date')
                                    ->label('End Date')
                                    ->required()
                                    ->disabled(!$user->hasAnyRole(['admin', 'sales_operation' , 'head_trainer']))
                                    ->helperText('Select the date when the task ends.'),
                                Select::make('task_type')
                                    ->options([
                                        'demo' => 'Demo',
                                        'training' => 'Training',
                                    ])
                                    ->label('Task Type')
                                    ->disabled(!$user->hasAnyRole(['admin', 'sales_operation' , 'head_trainer']))
                                    ->placeholder('Select the type of task')
                                    ->helperText('Choose the most relevant category for this task.'),

                                TextInput::make('time')
                                    ->disabled(!$user->hasAnyRole(['admin', 'sales_operation' , 'head_trainer' ]))
                                    ->label('Estimated Time (Hours)')
                                    ->placeholder('e.g., 2.5')
                                    ->helperText('Enter the estimated duration in hours.'),
                            ]),


                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('Task Location')
                    ->description('Select the location where this task will be carried out')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('district_id')
                                    ->label('District')
                                    ->options(District::pluck('name', 'id')->toArray())
                                    ->placeholder('Select a district')
                                    ->reactive()
                                    ->required()
                                    ->disabled(!$user->hasAnyRole(['admin', 'sales_operation' , 'head_trainer']))
                                    ->helperText('Select the district where the task is located.'),

                                Select::make('block_id')
                                    ->label('Block')
                                    ->disabled(!$user->hasAnyRole(['admin', 'sales_operation' , 'head_trainer' ]))
                                    ->options(function (callable $get) {
                                        $districtId = $get('district_id');
                                        if (!$districtId) {
                                            return [];
                                        }
                                        return Block::where('district_id', $districtId)->pluck('name', 'id')->toArray();
                                    })
                                    ->placeholder('Select a block')
                                    ->reactive()
                                    ->required()
                                    ->helperText('Select the block within the selected district.'),

                                Select::make('school_id')
                                    ->label('School')
                                    ->disabled(!$user->hasAnyRole(['admin', 'sales_operation', 'head_trainer']))
                                    ->options(function (callable $get) {
                                        $blockId = $get('block_id');
                                        if (!$blockId) {
                                            return [];
                                        }
                                        return School::where('block_id', $blockId)->pluck('name', 'id')->toArray();
                                    })
                                    ->placeholder('Select a school')
                                    ->reactive()
                                    ->required()
                                    ->helperText('Select the school where this task will take place.'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Assign Task')
                    ->description('Assign this task to a specific user')
                    ->schema([
                        Select::make('user_id')
                            ->label('Assign to User')
                            ->options(
                                User::whereHas('roles', function ($query) {
                                    $query->where('name', 'trainer'); // Filter users with the 'trainer' role
                                })
                                ->where('company_id', $user->company_id) // Filter by the company ID of the current user
                                ->pluck('name', 'id') // Get the user name and ID
                                ->toArray() // Convert to an array for options
                            )
                            ->preload()                            ->searchable()
                            ->placeholder('Assign this task to a user')
                            ->required()
                            ->hidden(!$user->hasAnyRole(['admin', 'sales_operation', 'head_trainer']))

                            ->helperText('Search and select a user to assign this task.'),
                    ])
                    ->hidden(!$user->hasAnyRole(['admin', 'sales_operation' , 'head_trainer']))

                    ->collapsible(),
            ])
            ->columns(1); // Display all sections in a single-column layout

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->sortable()->searchable(),
                TextColumn::make('description')->limit(50),
                TextColumn::make('user.name')->label('Assigned User'),
                TextColumn::make('start_date')->date(),
                TextColumn::make('end_date')->date(),
                TextColumn::make('created_at')->date()->label('Created On'),
            ])
            ->filters([
                Filter::make('Overdue')
                    ->query(fn($query) => $query->where('end_date', '<', now()))
                    ->label('Overdue Tasks'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
