<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesLeadManagementResource\Pages;
use App\Models\SalesLeadManagement;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Facades\DB;

class SalesLeadManagementResource extends Resource
{
    protected static ?string $model = SalesLeadManagement::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\Select::make('district')
                ->label('District')
                ->options(DB::table('districts')->pluck('name', 'id')) // Replace 'districts' with your districts table
                ->reactive()
                ->required(),

            Forms\Components\Select::make('block')
                ->label('Block')
                ->options(function (callable $get) {
                    $districtId = $get('district');
                    if (!$districtId) {
                        return [];
                    }
                    return DB::table('blocks')->where('district_id', $districtId)->pluck('name', 'id'); // Replace 'blocks' with your blocks table
                })
                ->reactive()
                ->required(),

            Forms\Components\Select::make('school')
                ->label('School')
                ->options(function (callable $get) {
                    $blockId = $get('block');
                    if (!$blockId) {
                        return [];
                    }
                    return DB::table('schools')->where('block_id', $blockId)->pluck('name', 'id'); // Replace 'schools' with your schools table
                })
                ->reactive()
                ->required(),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'new' => 'New',
                    'active' => 'Active',
                    'rejected' => 'Rejected',
                    'converted' => 'Converted',
                ])
                ->reactive()
                ->required(),

            Forms\Components\Textarea::make('feedback')
                ->label('Feedback')
                ->placeholder('Provide feedback here...')
                ->visible(fn (callable $get) => in_array($get('status'), ['active', 'rejected']))
                ->required(fn (callable $get) => in_array($get('status'), ['active', 'rejected'])),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('district')->label('District'),
            Tables\Columns\TextColumn::make('block')->label('Block'),
            Tables\Columns\TextColumn::make('school')->label('School'),
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
