<?php

namespace App\Filament\Resources\TrainerVisitResource\RelationManagers;

use App\Models\Block;
use App\Models\District;
use App\Models\School;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'visitedSchool';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
    
                

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
                ->required(),
                FileUpload::make('image')
                ->label('images')
    ->optimize('webp')                ->disk('s3')
                ->directory('CRM')
                ->required()
                ->helperText('Upload image'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('school.name')->label('School'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
           
    }
}
