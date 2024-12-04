<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\RelationManagers\PaymentRelationManager;
use App\Filament\Resources\SchoolResource\Pages;
use App\Filament\Resources\SchoolResource\RelationManagers\LeadStatusesRelationManager;
use App\Filament\Resources\SchoolResource\RelationManagers\SchoolPaymentRelationManager;
use App\Models\Block;
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


    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Select::make('block_id')
                ->label('Block')
                ->options(Block::all()->pluck('name', 'id')) // Fetch block names for the dropdown
                ->required()
                ->searchable()
                ->placeholder('Select a block'),

            TextInput::make('name')
                ->label('School Name')
                ->required()
                ->maxLength(255),

            Select::make('payment_status')
                ->label('Payment Status')
                ->options([
                    'New' => 'New',
                    'Pending Payment' => 'Pending Payment',
                    'Paid' => 'Paid',
                    'Partially Paid' => 'Partially Paid',
                    'Payment Overdue' => 'Payment Overdue',
                ])
                ->required()
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

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
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
            ]);
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
