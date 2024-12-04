<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesLeadManagementResource\Pages;
use App\Models\Block;
use App\Models\District;
use App\Models\SalesLeadManagement;
use App\Models\School;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Facades\DB;

class SalesLeadManagementResource extends Resource
{
    protected static ?string $model = SalesLeadManagement::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin' , 'sales' ]);
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('district_id')
            ->label('District')
            ->options(District::pluck('name', 'id')->toArray()) // Fetch districts using Eloquent
            ->reactive()
            ->required(),
        
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
            ->required(),
        
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
            ->required(),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'School Nurturing' => 'School Nurturing',
                    'active' => 'Active',
                    'rejected' => 'Rejected',
                    'converted' => 'Converted',
                ])
                ->reactive()
                ->required(),

                Forms\Components\Select::make('allocated_to')
                ->label('Allocated To')
                ->options(\App\Models\User::pluck('name', 'id'))
                ->hidden(fn() => !auth()->user()->hasRole('admin')) // Disable for non-admins
                ->default(auth()->id()) // Set default to current user's ID for non-admins
                ->required(),

            Forms\Components\Textarea::make('feedback')
                ->label('Feedback')
                ->placeholder('Provide feedback here...')
                ->visible(fn(callable $get) => in_array($get('status'), ['active', 'rejected']))
                ->required(fn(callable $get) => in_array($get('status'), ['active', 'rejected'])),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('district.name')->label('District'),
            Tables\Columns\TextColumn::make('block.name')->label('Block'),
            Tables\Columns\TextColumn::make('school.name')->label('School'),
            Tables\Columns\TextColumn::make('status')->label('Status'),
            Tables\Columns\TextColumn::make('feedback')->label('Feedback')->limit(50),
        ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesLeadManagement::route('/'),
            'create' => Pages\CreateSalesLeadManagement::route('/create'),
            'edit' => Pages\EditSalesLeadManagement::route('/{record}/edit'),
        ];
    }
}
