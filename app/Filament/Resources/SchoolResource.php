<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\RelationManagers\PaymentRelationManager;
use App\Filament\Resources\SchoolResource\Pages;
use App\Filament\Resources\SchoolResource\RelationManagers\LeadStatusesRelationManager;
use App\Filament\Resources\SchoolResource\RelationManagers\SchoolPaymentRelationManager;
use App\Models\Block;
use App\Models\District;
use App\Models\School;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class SchoolResource extends Resource
{
    protected static ?string $model = School::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Utilities';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'sales_operation' , 'sales_operation_head' ,]);
    }



    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            // Select::make('block_id')
            //     ->label('Block')
            //     ->options(Block::all()->pluck('name', 'id')) // Fetch block names for the dropdown
            //     ->required()
            //     ->searchable()
            //     ->placeholder('Select a block'),


            Select::make('district_id')
                ->label('District')
                ->options(District::pluck('name', 'id')->toArray())
                ->placeholder('Select a district')
                ->reactive()
                ->required()
                // ->disabled(!$user->hasAnyRole(['admin', 'sales_operation']))
                ->helperText('Select the district where the task is located.'),

            Select::make('block_id')
                ->label('Block')
                // ->disabled(!$user->hasAnyRole(['admin', 'sales_operation']))
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

            // Select::make('school_id')
            //     ->label('School')
            //     // ->disabled(!$user->hasAnyRole(['admin', 'sales_operation']))
            //     ->options(function (callable $get) {
            //         $blockId = $get('block_id');
            //         if (!$blockId) {
            //             return [];
            //         }
            //         return School::where('block_id', $blockId)->pluck('name', 'id')->toArray();
            //     })
            //     ->placeholder('Select a school')
            //     ->reactive()
            //     ->required()
            //     ->helperText('Select the school where this task will take place.'),

            TextInput::make('name')
                ->label('School Name')
                ->required()
                ->maxLength(255),

            Select::make('board_id')
                ->label('Board')
                ->relationship('board', 'name') // Assumes the `School` model has a `name` attribute
                ->required(),

            Select::make('payment_status')
                ->label('Payment Status')
                ->options([
                    'New' => 'New',
                    'Paid' => 'Paid',
                    'Partially Paid' => 'Partially Paid',
                ])
                ->disabled()
                ->hidden(fn (string $context) => $context === 'create')

                ->placeholder('Select a payment status'),

            // Select::make('process_status')
            //     ->label('Process Status')
            //     ->options([
            //         'Principal Meeting' => 'Principal Meeting',
            //         'School Nurturing' => 'School Nurturing',
            //         'Lead Re-engaged' => 'Lead Re-engaged',
            //         'Demo Scheduled' => 'Demo Scheduled',
            //         'Demo Reschedule' => 'Demo Reschedule',
            //         'Demo Completed' => 'Demo Completed',
            //         'Deal Lost' => 'Deal Lost',
            //         'Deal Won' => 'Deal Won',
            //     ])
            //     ->required()
            //     ->placeholder('Select a process status'),

            DatePicker::make('demo_date')
                ->label('Demo Date')
                ->reactive()
                ->visible(fn($get) => $get('process_status') == 'Demo Scheduled') // Conditional visibility
                ->required()
                ->placeholder('Select a demo date'),


            Textarea::make('address')
                ->label('School Address')
                ->required(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('block.name')
                    ->label('Block')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('School Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'New' => 'gray',
                            'Pending Payment' => 'yellow',
                            'Paid' => 'green',
                            'Partially Paid' => 'orange',
                            'Payment Overdue' => 'red',
                            'Principal Meeting' => 'blue',
                            'School Nurturing' => 'purple',
                            'Lead Re-engaged' => 'cyan',
                            'Demo Reschedule' => 'indigo',
                            'Demo Completed' => 'teal',
                            'Demo Scheduled' => 'lime',
                            'Deal Lost' => 'red',
                            'Deal Won' => 'green',
                            default => 'gray',
                        };
                    })
                    ->sortable()
                    ->searchable(),


                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'New' => 'New',
                        'Pending Payment' => 'Pending Payment',
                        'Paid' => 'Paid',
                        'Partially Paid' => 'Partially Paid',
                        'Payment Overdue' => 'Payment Overdue',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->where('status', $data['value']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->paginated([10, 25,]);

    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\SchoolResource\RelationManagers\InvoicesRelationManager::class,
            \App\Filament\Resources\SchoolResource\RelationManagers\BookRelationManager::class,
            LeadStatusesRelationManager::class,
            SchoolPaymentRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchools::route('/'),
            'create' => Pages\CreateSchool::route('/create'),
            'edit' => Pages\EditSchool::route('/{record}/edit'),
            'view' => Pages\ViewUser::route('/{record}/view'),
        ];
    }
}
