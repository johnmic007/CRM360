<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceLogResource\RelationManagers\InvoiceLogRelation;
use App\Filament\Resources\InvoiceLogResource\RelationManagers\InvoiceLogRelationManager;
// use App\Filament\Resources\InvoiceLogResource\RelationManagers\NameRelationManager;
use App\Filament\Resources\InvoResource\Pages;
use App\Filament\Resources\InvoResource\RelationManagers;
use App\Filament\Resources\InvoResource\Widgets\InvoiceStats;
use App\Helpers\InvoiceHelper;
use App\Models\Invo;
use App\Models\Invoice;
use App\Models\Items;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\User;
use App\Models\Role;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\HasManyRepeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup as ActionsActionGroup;
use Filament\Tables\Actions\GroupAction;

class InvoResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $label = 'MOU'; // Singular form
    protected static ?string $pluralLabel = 'MOUs';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'head', 'sales_operation']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Invoice Details Section

            TextInput::make('invoice_number')
                ->label('MOU Number')
                ->required(),

            Section::make('MOU Details')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('school_id')
                                ->label('School')
                                ->relationship('school', 'name')
                                ->required()
                                ->reactive()
                                ->searchable()
                                ->default(fn() => request()->query('school_id')), // Set default value from query parameter


                            Select::make('company_id')
                                ->label('Company')
                                ->relationship('company', 'name')
                                ->required()
                                ->hidden()
                                ->default(fn() => auth()->user()->company_id) // Set default from logged-in user's company_id
                                ->disabled(), // Make the field non-editable


                            TextInput::make('students_count')
                                ->label('No of students')
                                ->numeric()
                                ->default(0)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                    // Update quantities in items
                                    $items = $get('items') ?? [];
                                    foreach ($items as $index => $item) {
                                        $set('items.' . $index . '.quantity', $state);
                                        // Update total for each item
                                        $price = $item['price'] ?? 0;
                                        $set('items.' . $index . '.total', $state * $price);
                                    }
                                    // Update books_count in books
                                    // $books = $get('books') ?? [];
                                    // foreach ($books as $index => $book) {
                                    //     $set('books.' . $index . '.books_count', $state);
                                    //     // Update total for each book
                                    //     $price = $book['price'] ?? 0;
                                    //     $set('books.' . $index . '.total', $state * $price);
                                    // }
                                    // // Recalculate total amount
                                    // $totalAmount = InvoiceHelper::calculateTotalAmount($get('items'), $get('books'));
                                    // $set('total_amount', $totalAmount);
                                    // // Recalculate total number of books
                                    // $totalBooks = array_sum(array_column($get('books') ?? [], 'books_count'));
                                    // $set('books_count', $totalBooks);
                                }),


                            DatePicker::make('issue_date')
                                ->label('Issue Date')
                                ->required(),

                            DatePicker::make('due_date')
                                ->label('Due Date')
                                ->nullable(),

                            Select::make('status')
                                ->label('Status')
                                ->options([
                                    'draft' => 'Draft',
                                    'sent' => 'Sent',
                                    'paid' => 'Paid',
                                ])
                                ->default('draft'),
                        ]),
                ])
                ->collapsible()
                ->collapsed(false),

            // Invoice Items Section
            Section::make('MOU Items')
                ->schema([
                    HasManyRepeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Select::make('item_id') // Change to a select input for items
                                ->label('Item')
                                ->options(Items::pluck('name', 'id')->toArray()) // Fetch items from the Items model
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function (callable $set, $get, $state) {
                                    $item = Items::find($state); // Find the selected item
                                    if ($item) {
                                        $set('price', $item->price); // Set the price from the selected item
                                        // Update the total based on quantity and price
                                        $quantity = $get('quantity') ?? 0;
                                        $set('total', $quantity * $item->price);
                                    }
                                }),


                            Grid::make(3)
                                ->schema([


                                    TextInput::make('quantity')
                                        ->label('Quantity')
                                        ->numeric()
                                        ->default(fn($get) => $get('../../students_count') ?? 0)
                                        ->readOnly()
                                        ->reactive()
                                        ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                            $price = $get('price') ?? 0;
                                            $set('total', $state * $price);
                                            // Recalculate total amount
                                            $totalAmount = InvoiceHelper::calculateTotalAmount($get('../../items'), $get('../../books'));
                                            $set('../../total_amount', $totalAmount);
                                        }),


                                    TextInput::make('price')
                                        ->label('Price')
                                        ->numeric()
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function (callable $set, $get, $state) {
                                            // Update the total for this item based on quantity and price
                                            $quantity = $get('quantity') ?? 0;
                                            $price = $get('price') ?? 0;
                                            $set('total', $quantity * $price); // Set the total for the item
                                        }),

                                    TextInput::make('total')
                                        ->label('Total')
                                        ->numeric()
                                        ->readonly()
                                        ->default(0),
                                ]),
                        ])
                        ->reactive()
                        ->afterStateUpdated(function (callable $set, $get) {
                            $items = $get('items') ?? [];
                            $totalAmount = InvoiceHelper::calculateTotalAmount($items);
                            $set('total_amount', $totalAmount);
                        })
                        ->createItemButtonLabel('Add Item'),
                ])
                ->collapsible()
                ->collapsed(false),

            Section::make('Add Books')
                ->schema([
                    Repeater::make('books')
                        ->relationship('books') // Refers to the books relationship in the model
                        ->schema([
                            Grid::make(2) // Two fields in one row for each repeater entry
                                ->schema([

                                    Select::make('book_id')
                                        ->label('Book')
                                        ->options(
                                            \App\Models\Book::whereNotNull('title')->pluck('title', 'id')->toArray()
                                        )
                                        ->searchable()
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(function (callable $set, $get, $state) {
                                            // Get the price of the selected book
                                            $bookPrice = \App\Models\Book::find($state)->price ?? 0;

                                            // Update the price field dynamically
                                            $set('price', $bookPrice);

                                            // Recalculate total after price change
                                            $quantity = $get('books.0.books_count') ?? 0; // Get quantity from first book (assuming all have the same quantity)
                                            $set('total', $quantity * $bookPrice); // Update the total for the current book
                                        }),

                                    TextInput::make('school_id')
                                        ->hidden()
                                        ->default(fn(callable $get) => $get('../../school_id')), // Get the parent `school_id`

                                    TextInput::make('books_count')
                                        ->label('Quantity')
                                        ->numeric()
                                        ->default(fn($get) => $get('../../students_count') ?? 0)
                                        // ->disabled()
                                        ->reactive()
                                        ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                            // Recalculate total number of books
                                            $books = $get('../../books') ?? [];
                                            $totalBooks = array_sum(array_column($books, 'books_count'));
                                            $set('../../books_count', $totalBooks);
                                        }),

                                    // ->afterStateUpdated(function (callable $set, $get, $state) {

                                    //     $set('total', InvoiceHelper::calculateTotalAmount($get('items')));

                                    //     // Recalculate total based on quantity and price when quantity is updated
                                    //     $quantity = $state;
                                    //     $price = $get('price') ?? 0;
                                    //     $set('total', $quantity * $price);
                                    // }),

                                    // TextInput::make('price')
                                    //     ->label('Price')
                                    //     ->numeric()
                                    //     ->readonly()
                                    //     ->default(0),

                                    // TextInput::make('total')
                                    //     ->label('Total')
                                    //     ->numeric()
                                    //     ->readonly()
                                    //     ->default(0),
                                ]),
                        ])
                        ->grid([
                            'default' => 2, // Ensures two repeater items appear per row
                        ])
                        ->reactive() // Reactively update books repeater when parent changes
                        ->default(fn($get) => collect($get('books'))->map(function ($book) use ($get) {
                            return array_merge($book, ['school_id' => $get('school_id')]);
                        }))
                        ->afterStateUpdated(function (callable $set, callable $get) {
                            // Recalculate total number of books
                            $books = $get('books') ?? [];
                            $totalBooks = array_sum(array_column($books, 'books_count'));
                            $set('books_count', $totalBooks);
                        })
                        ->createItemButtonLabel('Add Book') // Button label for adding books
                        ->reactive()

                ])
                ->collapsible()
                ->collapsed(false),


            // Total Amount Section
            Section::make('Summary')
                ->schema([


                    Grid::make(2)
                        ->schema([
                            TextInput::make('students_count')
                                ->label('Total no of students')
                                ->numeric()
                                ->readOnly()
                                ->default(0),

                            TextInput::make('books_count')
                                ->label('Total no of Books')
                                ->numeric()
                                ->default(0)
                                ->readOnly()
                                ->reactive()
                                ->formatStateUsing(function (callable $get) {
                                    $books = $get('books') ?? [];
                                    $totalBooks = array_sum(array_column($books, 'books_count'));
                                    return $totalBooks;
                                }),

                            

                            DatePicker::make('validity_start')
                                ->label('Validity Start')
                                ->required()
                                ->placeholder('Select start date'),

                            DatePicker::make('validity_end')
                                ->label('Validity End')
                                ->required()
                                ->placeholder('Select end date')
                                ->afterOrEqual('validity_start'),

                                Toggle::make('trainer_required')
                                ->label('Trainer Required')
                                ->default(0),

                        ]),

                    Grid::make(1)
                        ->schema([
                            TextInput::make('total_amount')
                                ->label('Total Amount')
                                ->numeric()
                                ->readOnly()
                                ->default(0)
                                ->dehydrated() // Ensure it is saved
                                ->reactive()
                                ->formatStateUsing(function (callable $get) {
                                    $items = $get('items') ?? [];
                                    $totalAmount = InvoiceHelper::calculateTotalAmount($items);
                                    return $totalAmount;
                                })
                                ->extraAttributes(['class' => 'text-xl font-bold']),
                        ]),



                ])
                ->collapsible()
                ->collapsed(false),


            Section::make('Deal Closure Details')
                ->schema([
                    Select::make('closed_by')
                        ->label('Closed By')
                        ->options(function () {
                            $currentUser = auth()->user();


                            // Get subordinates with specific roles (BDA and BDM) and the same company_id
                            $subordinates = User::query()
                                ->where('company_id', $currentUser->company_id) // Filter by the same company_id
                                ->whereHas('roles', function ($query) {
                                    $query->whereIn('name', ['BDA', 'BDM']); // Filter roles to BDA and BDM
                                })
                                ->pluck('name', 'id');



                            return $subordinates;
                        })
                        ->preload()
                        ->required()
                        ->searchable(),



                    Textarea::make('description')
                        ->label('Description')
                        ->placeholder('Provide additional details about the deal closure')
                        ->required()
                        ->maxLength(255),

                ])
                ->collapsible()
                ->collapsed(false),

                FileUpload::make('files')->multiple()



            // Payment Section
            // Section::make('Payment')
            //     ->schema([
            //         TextInput::make('total_amount')
            //             ->label('Total Amount')
            //             ->numeric()
            //             ->disabled()
            //             ->default(0),

            //         TextInput::make('paid')
            //             ->label('Amount Paid')
            //             ->numeric()
            //             ->disabled()
            //             ->default(0),

            //         TextInput::make('amount')
            //             ->label('Amount to Pay')
            //             ->numeric()
            //             ->minValue(0)
            //             ->maxValue(fn ($get) => $get('total_amount') - $get('paid'))
            //             ->required(fn ($get) => ($get('total_amount') - $get('paid')) > 0)
            //             ->visible(fn ($get) => ($get('total_amount') - $get('paid')) > 0)
            //             ->reactive(),
            //     ])
            //     ->collapsible()
            //     ->collapsed(false),


            // Textarea::make('description')
            //     ->label('Description')
            //     ->placeholder('Provide additional details about the deal closure')
            //     ->required()
            //     ->maxLength(255),


        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->query(function (Builder $query) {
            //     // dd($query->company_id);
            //     $user = auth()->user();

            //     if (!$user || !$user->company_id) {
            //         return $query; // Return all records if no user or company_id
            //     }

            //     return $query->where('company_id', $user->company_id);
            // })


            ->columns([
                TextColumn::make('invoice_number')
                    ->label('MOU Number')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('school.name')
                    ->label('School')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('company.name')
                    ->label('Company')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('INR') // Adjust the currency as needed
                    ->sortable(),

                TextColumn::make('paid')
                    ->label('Paid')
                    ->money('USD') // Adjust the currency as needed
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->color(function ($state) {
                        return match ($state) {
                            'draft' => 'gray',
                            'sent' => 'yellow',
                            'paid' => 'green',
                            default => 'gray',
                        };
                    })
                    ->badge() // Adds the badge styling to the column
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('school_id')
                    ->label('School')
                    ->relationship('school', 'name'),

                SelectFilter::make('company_id')
                    ->label('Company')
                    ->relationship('company', 'name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'paid' => 'Paid',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
   
                
                ActionsActionGroup::make([
              
                        Action::make('Download PDF')
                            ->label('Download Invoice PDF')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->url(fn(Invoice $record) => route('invoice.download', $record->id))
                            ->openUrlInNewTab(),
                        Action::make('Download Curriculum PDF')
                            ->label('Download Curriculum PDF')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->url(fn(Invoice $record) => route('invoice.downloadCurriculum', $record->id))
                            ->openUrlInNewTab(),

                ])
                ->icon('heroicon-o-arrow-down-on-square-stack')
                ->dropdownWidth(MaxWidth::ExtraSmall),
                


                Tables\Actions\Action::make('Pay')
                    ->label('Pay')
                    ->icon('heroicon-o-credit-card')
                    ->color('primary')
                    ->modalHeading('Pay MOU')
                    ->form([
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->disabled()
                            ->default(fn(Invoice $record) => $record->total_amount), // Display total amount
                        TextInput::make('paid')
                            ->label('Amount Paid')
                            ->numeric()
                            ->disabled()
                            ->default(fn(Invoice $record) => $record->paid), // Display paid amount
                        TextInput::make('amount')
                            ->label('Amount to Pay')
                            ->numeric()
                            ->required()
                            ->rules([
                                'numeric',
                                'min:0',
                            ])
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $get, $state) {
                                if ($state > $get('total_amount') - $get('paid')) {
                                    $set('amount', $get('total_amount') - $get('paid')); // Prevent overpayment
                                }
                            }),
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'cash' => 'Cash',
                                'check' => 'Check',
                                'bank_transfer' => 'Bank Transfer',
                            ])
                            ->required(),
                        DatePicker::make('payment_date')
                            ->required(),

                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->nullable(),
                        // TextInput::make('transaction_reference')
                        //     ->label('Transaction Reference')
                        //     ->nullable(),
                        FileUpload::make('payment_proof')
                            ->label('Payment Proof')
                            ->image()  // Specify that this is an image
                            ->directory('payment_proofs')  // Store the image in a specific directory
                            ->nullable(),
                    ])
                    ->action(function (array $data, Invoice $record) {
                        // Handle file upload for payment proof
                        $paymentProofPath = null;
                        if (isset($data['payment_proof'])) {
                            $paymentProofPath = $data['payment_proof']->store('payment_proofs', 'public');
                        }

                        // Process the payment
                        $remaining = $record->total_amount - $record->paid;
                        $payment = min($data['amount'], $remaining);

                        // Update the invoice with the new paid amount and status
                        $record->update([
                            'paid' => $record->paid + $payment,
                            'status' => $record->paid + $payment >= $record->total_amount ? 'paid' : $record->status,
                        ]);

                        // If there's still a balance, calculate the next payment date (e.g., 30 days from now)
                        if ($record->paid < $record->total_amount) {
                            $nextPaymentDate = now()->addDays(30)->toDateString();  // Set next payment due date
                        } else {
                            $nextPaymentDate = null;  // No further payment due
                        }

                        // Create a log entry for the payment in invoice_logs
                        $record->logs()->create([
                            'type' => 'payment',
                            'payment_method' => $data['payment_method'],
                            'reference_number' => $data['reference_number'],
                            // 'transaction_reference' => $data['transaction_reference'],
                            'payment_proof' => $paymentProofPath,
                            'payment_date' => $data['payment_date'],
                            'paid_amount' => $payment,
                            'description' => 'Paid amount: ' . $payment,
                            'next_payment_due' => $nextPaymentDate,  // Store next payment date in the log
                        ]);
                    })
                    ->requiresConfirmation()
                    ->visible(fn(Invoice $record) => $record->paid < $record->total_amount),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getWidgets(): array
    {
        return [
            InvoiceStats::class,
        ];
    }


    public static function getRelations(): array
    {
        return [
            InvoiceLogRelation::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvos::route('/'),
            'create' => Pages\CreateInvo::route('/create'),
            'edit' => Pages\EditInvo::route('/{record}/edit'),
        ];
    }
}
