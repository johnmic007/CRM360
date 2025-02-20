<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountsClosingResource\Pages;
use App\Filament\Resources\AccountsClosingResource\RelationManagers;
use App\Filament\Resources\WalletLogResource\RelationManagers\AssociatedDebitsRelationManager;
use App\Models\AccountsClosing;
use App\Models\TrainerVisit;
use App\Models\User;
use App\Models\WalletLog;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\View;
use Filament\Tables\Actions\ActionGroup as ActionsActionGroup;


class AccountsClosingResource extends Resource
{
    protected static ?string $model = WalletLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $label = 'Accounts Closing'; // Singular form

    protected static ?string $pluralLabel = 'Accounts Closing';

    protected static ?string $navigationGroup = 'Finance Management';


    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole(['admin', 'accounts_head' , 'company' , 'sales_operation_head' ,'sales_operation']);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->hasRole(['admin', 'accounts_head' ]);

    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('amount')->label('Amount')->disabled(),
                TextInput::make('balance')->label('Balance')->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('User'),
                TextColumn::make('amount')->label('Amount')->money('INR'),
                TextColumn::make('balance')->label('Balance')->money('INR'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge() // Use badge styling
                    ->colors([
                        'success' => 'credit',
                        'danger' => 'debit',
                    ]),
                TextColumn::make('description')->label('Description'),
                TextColumn::make('created_at')->label('Date')->dateTime(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                ->label('User')
                ->options(User::pluck('name', 'id')->toArray())
                ->query(function (Builder $query, array $data) {
                    if (!empty($data['value'])) {
                        $query->where('user_id', $data['value']);
                    }
                }),
            ], layout: FiltersLayout::AboveContent  )


            ->actions([
                Tables\Actions\EditAction::make(),

                ActionsActionGroup::make([

                    Tables\Actions\Action::make('Download PDF 1')
                ->action(fn ($record) => self::downloadAccountsClosingPDF1($record))
                ->icon('heroicon-o-arrow-down-tray'),

                    Tables\Actions\Action::make('Download PDF 2')
                    ->action(fn ($record) => self::downloadAccountsClosingPDF2($record))
                    ->icon('heroicon-o-arrow-down-tray'),

                    Tables\Actions\Action::make('Download PDF 3')
                    ->action(fn ($record) => self::downloadAccountsClosingPDF3($record))
                    ->icon('heroicon-o-arrow-down-tray'),
                ])
            ])
            ->paginated([10, 25,]);
    }

    public static function downloadAccountsClosingPDF1(WalletLog $walletLog)
    {
        // Fetch all associated debits using the relationship
        $associatedDebits = $walletLog->associatedDebits()->with('trainerVisit')->get();
        $closingData = $walletLog->associatedDebits ?? collect();

        // Render the Blade template with the fetched data
        $html = View::make('pdf.accounts_closing1', [
            'walletLog' => $walletLog,
            'walletLogs' => $associatedDebits, // Pass associated debits
            'closingData' => $closingData,
        ])->render();

        // Initialize Dompdf
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Return the PDF as a download
        return response()->streamDownload(
            fn () => print($dompdf->output()),
            'accounts_closing_' . $walletLog->id . '_' . now()->format('YmdHis') . '.pdf'
        );
    }

    public static function downloadAccountsClosingPDF2(WalletLog $walletLog)
{
    // Fetch all associated debits using the relationship
    $associatedDebits = $walletLog->associatedDebits()->with('trainerVisit')->get();
    $closingData = $walletLog->associatedDebits ?? collect();

    // Render the Blade template with the fetched data
    $html = View::make('pdf.accounts_closing2', [
        'walletLog' => $walletLog,
        'walletLogs' => $associatedDebits, // Pass associated debits
        'closingData' => $closingData,
    ])->render();

    // Initialize Dompdf
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Return the PDF as a download
    return response()->streamDownload(
        fn () => print($dompdf->output()),
        'accounts_closing_' . $walletLog->id . '_' . now()->format('YmdHis') . '.pdf'
    );
}

public static function downloadAccountsClosingPDF3(WalletLog $walletLog)
{
    // Fetch all associated debits ensuring trainerVisit and school relationships exist
    $associatedDebits = $walletLog->associatedDebits()
    ->whereNotNull('trainer_visit_id')
    ->with(['trainerVisit.salesLeadStatus.school.district']) // Load school through SalesLeadStatus
    ->get();

    // foreach ($associatedDebits as $debit) {
    //     if ($debit->trainerVisit && $debit->trainerVisit->salesLeadStatus) {
    //         dd($debit->trainerVisit->salesLeadStatus);
    //     }
    // }


        $formattedLogs = $associatedDebits->map(function ($log) {
            return [
                'date' => optional($log->trainerVisit)->visit_date 
                    ? \Carbon\Carbon::parse($log->trainerVisit->visit_date)->format('d/m/Y') 
                    : 'N/A',
                'school_name' => optional($log->trainerVisit->school)->name ?? 'N/A',
                'school_address' => optional($log->trainerVisit->school)->address ?? 'N/A',
                // 'district_name' => optional($log->trainerVisit->school->district)->name ?? 'N/A',
            ];
        });

        // dd($formattedLogs);

    // Render the Blade template with the fetched data
    $html = View::make('pdf.accounts_closing3', [
        'walletLog' => $walletLog,
        'formattedLogs' =>$formattedLogs,
        'walletLogs' => $associatedDebits, // Pass associated debits
    ])->render();

    // Initialize Dompdf
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Return the PDF as a download
    return response()->streamDownload(
        fn () => print($dompdf->output()),
        'accounts_closing_' . $walletLog->id . '_' . now()->format('YmdHis') . '.pdf'
    );
}



    public static function getRelations(): array
    {
        return [
            AssociatedDebitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccountsClosings::route('/'),
            'create' => Pages\CreateAccountsClosing::route('/create'),
            'edit' => Pages\EditAccountsClosing::route('/{record}/edit'),
        ];
    }
}
