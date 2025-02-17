<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExtraExpensesResource\Pages;
use App\Filament\Resources\ExtraExpensesResource\RelationManagers;
use App\Filament\Resources\ExtraExpensesResource\Widgets\ExpenseStats;
use App\Models\ExtraExpenseCategory;
use App\Models\ExtraExpenses;
use App\Models\TrainerVisit;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
    use Filament\Facades\Filament;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
    
    use Illuminate\Support\Facades\Auth;
    



class ExtraExpensesResource extends Resource
{
    protected static ?string $model = TrainerVisit::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $label = 'Extra Expnenses'; // Singular form
    protected static ?string $pluralLabel = 'Extra Expnenses';

    
    

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'sales_head' , 'head' , 'sales_operation']);
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->hasRole(['admin']);

        
    }

  
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Expense Details')
                    ->description('Fill in the details for your extra expense.')
                    ->collapsible()
                    ->schema([
                        Hidden::make('travel_type')->default('extra_expense'),
    
                        Grid::make(2) // ✅ Aligns fields in a responsive 2-column layout
                            ->schema([
                                TextInput::make('total_expense')
                                    ->label('💰 Amount')
                                    ->numeric()
                                    ->required()
                                    ->prefix('₹')
                                    ->placeholder('Enter the expense amount')
                                    ->helperText('Specify the total cost for this expense.')
                                    ->columnSpan(1), 
    
                                DatePicker::make('visit_date')
                                    ->label('📅 Visited Date')
                                    ->required()
                                    ->native(false)
                                    ->placeholder('Select the date of visit')
                                    ->columnSpan(1), 
                            ]),
    
                        Textarea::make('description')
                            ->label('📝 Description')
                            ->required()
                            ->placeholder('Enter details about this extra expense...')
                            ->rows(4)
                            ->helperText('Provide a short reason for the expense.')
                            ->columnSpanFull(),
                    ]),
    
                Section::make('Supporting Documents')
                    ->description('Upload related images for this expense.')
                    ->schema([
                        FileUpload::make('travel_bill')
                            ->label('🖼 Upload Receipts')
                            ->disk('s3')
                            ->directory('CRM')
                            ->multiple()
                            ->imagePreviewHeight('100')
                            ->imageEditor()
                            ->maxSize(5 * 1024) // 5MB limit
                            ->helperText('Upload multiple images of bills or receipts.')
                            ->columnSpanFull(),
                    ]),
    
                Section::make('Category Selection')
                    ->description('Select or add a category for this expense.')
                    ->schema([
                        Select::make('category')
                            ->label('📂 Expense Category')
                            ->options(
                                ExtraExpenseCategory::pluck('name', 'name')->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->placeholder('Select or add a category...')
                            ->helperText('Choose the category that best fits this expense.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
    


    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name'),
                TextColumn::make('total_expense')->label('Amount'),
                TextColumn::make('visit_date')->label('Visited Date'),
                TextColumn::make('category')->label('Category'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                ->label('Filter by Category')
                ->options(ExtraExpenseCategory::pluck('name', 'name')->toArray())
                ->searchable()
                ->preload()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
            
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
{
    return [
        ExpenseStats::class, // Add the ExpenseStats widget
    ];
}


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExtraExpenses::route('/'),
            'create' => Pages\CreateExtraExpenses::route('/create'),
            'edit' => Pages\EditExtraExpenses::route('/{record}/edit'),
        ];
    }



    

}
