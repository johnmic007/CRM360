<?php

namespace App\Filament\Resources\SchoolResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder; // Add this import

class LeadStatusesRelationManager extends RelationManager
{
    protected static string $relationship = 'leadStatuses';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('status')
                ->label('Status')
                ->required()
                ->helperText('Enter the current status of the lead.'),

            Textarea::make('remarks')
                ->label('Remarks')
                ->rows(3)
                ->required()
                ->placeholder('Add relevant remarks or details about this status.'),

            TextInput::make('contacted_person')
                ->label('Contacted Person')
                ->required()
                ->placeholder('Enter the name of the contacted person.'),

            TextInput::make('contacted_person_designation')
                ->label('Contacted Person Designation')
                ->required()
                ->placeholder('Enter the designation (e.g., Principal, Teacher).'),

                Hidden::make('visited_by')

                ->default(auth()->id()), // Set default to the current user's ID


            DatePicker::make('visited_date')
                ->label('Visited Date')
                ->required()
                ->default(now())
                ->helperText('Select the date when the visit occurred.'),

            DatePicker::make('follow_up_date')
                ->label('Follow-Up Date')
                ->required()
                ->placeholder('Select the follow-up date for this lead.'),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(50),
                    Tables\Columns\TextColumn::make('block.name')->label('Block'),

                TextColumn::make('contacted_person')
                    ->label('Contacted Person'),
                TextColumn::make('contacted_person_designation')
                    ->label('Designation'),
                TextColumn::make('visited_by')
                    ->label('Visited By')
                    ->getStateUsing(fn($record) => $record->visitedBy?->name ?? 'Unknown'),
                TextColumn::make('follow_up_date')
                    ->label('Follow-Up Date')
                    ->date(),
                TextColumn::make('visited_date')
                    ->label('Visited Date')
                    ->date(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(), // Default view action
                Tables\Actions\EditAction::make(), // Default view action

            ])
            ->filters([
                Tables\Filters\Filter::make('visited_date')
                ->form([
                    DatePicker::make('start')->label('Start Date'),
                    DatePicker::make('end')->label('End Date'),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query
                        ->when($data['start'], fn($query, $date) => $query->whereDate('visited_date', '>=', $date))
                        ->when($data['end'], fn($query, $date) => $query->whereDate('visited_date', '<=', $date));
                })
                ->label('Visited Date'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(), // Enable the Create action
            ]);
    }
}
