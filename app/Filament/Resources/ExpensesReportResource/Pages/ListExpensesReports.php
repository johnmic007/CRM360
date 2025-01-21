<?php

namespace App\Filament\Resources\ExpensesReportResource\Pages;

use App\Filament\Resources\ExpensesReportResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http; // For making an HTTP request



class ListExpensesReports extends ListRecords
{
    protected static string $resource = ExpensesReportResource::class;



    protected function getHeaderActions(): array
{
    return [
        // Total Expenses
        Actions\Action::make('total_expenses')
            ->label(function () {
                $query = $this->getTableQuery();
                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }
                $totalExpenses = $query->sum('total_expense');
                return "Total Expenses: ₹" . number_format($totalExpenses, 2);
            })
            ->color('success')
            ->icon('heroicon-o-calculator')
            ->disabled(), // Display-only action

        // Total Requests
        Actions\Action::make('total_requests')
            ->label(function () {
                $query = $this->getTableQuery();
                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }
                $totalRequests = $query->count();
                return "Total Requests: {$totalRequests}";
            })
            ->color('primary')
            ->icon('heroicon-o-document-text')
            ->disabled(), // Display-only action

        // Approved Expenses
        Actions\Action::make('approved_expenses')
            ->label(function () {
                $query = $this->getTableQuery();
                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }
                $approvedExpenses = $query->where('approval_status', 'approved')->sum('total_expense');
                return "Approved Expenses: ₹" . number_format($approvedExpenses, 2);
            })
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->disabled(), // Display-only action


            Actions\Action::make('download_pdf')
            ->label('PDF')
            ->color('success')

            ->icon('heroicon-o-arrow-down-tray')
            ->action('downloadPDF'),

        // Rejected Expenses
        // Actions\Action::make('rejected_expenses')
        //     ->label(function () {
        //         $query = $this->getTableQuery();
        //         if (method_exists($this, 'applyFiltersToTableQuery')) {
        //             $this->applyFiltersToTableQuery($query);
        //         }
        //         $rejectedExpenses = $query->where('approval_status', 'rejected')->sum('total_expense');
        //         return "Rejected Expenses: ₹" . number_format($rejectedExpenses, 2);
        //     })
        //     ->color('danger')
        //     ->icon('heroicon-o-x-circle')
        //     ->disabled(), // Display-only action
    ];
}


public function downloadPDF()
{
    $query = $this->getTableQuery();

    // Apply filters to the query
    if (method_exists($this, 'applyFiltersToTableQuery')) {
        $this->applyFiltersToTableQuery($query);
    }

    // Fetch filtered data
    $records = $query->with('user')->get();

    $vDate = request()->input('visit_date'); // Assuming `start_date` is passed in the request

    $Date = request()->input('date'); // Assuming `start_date` is passed in the request




    // Calculate totals
    $totalKm = $records->sum('distance_traveled');
    $totalExpenses = $records->sum('total_expense');
    $totalWalletBalance = $records->pluck('user.wallet_balance')->sum();
    $totalApprovedExp   = $records->where('approval_status', 'approved')->sum('total_expense');
    $totalPendingExp    = $records->where('approval_status', 'pending')->sum('total_expense');
    $totalRequests      = $records->count();
    // Count travel modes
    $travelModeCounts = $records->groupBy('travel_mode')->map->count();
    $totalCars = $travelModeCounts['car'] ?? 0;
    $totalBikes = $travelModeCounts['bike'] ?? 0;


    $groupedByDate = $records->groupBy(function ($record) {
        return $record->visit_date->format('Y-m-d');
    });


    $labels = [];
    $dataTotalExpense    = [];
    $dataApprovedExpense = [];

    foreach ($groupedByDate as $date => $items) {
        $labels[] = $date;
        $dataTotalExpense[]    = $items->sum('total_expense');
        $dataApprovedExpense[] = $items->where('approval_status', 'approved')->sum('total_expense');
    }

    $chartConfig = [
        'type' => 'bar',
        'data' => [
            'labels'   => $labels,
            'datasets' => [
                [
                    'label' => 'Total Expense',
                    'data'  => $dataTotalExpense,
                    'backgroundColor' => '#3498db',
                ],
                [
                    'label' => 'Approved Expense',
                    'data'  => $dataApprovedExpense,
                    'backgroundColor' => '#2ecc71',
                ],
            ],
        ],
        'options' => [
            'title' => [
                'display' => true,
                'text'    => 'Total vs. Approved Expense by Date',
                'fontSize'=> 16,
            ],
            'scales' => [
                'yAxes' => [
                    ['ticks' => ['beginAtZero' => true]],
                ],
            ],
        ],
    ];

    // Build the QuickChart URL
    $chartUrl  = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartConfig));
    $response  = Http::get($chartUrl);
    $chartBase64 = base64_encode($response->body());

    // Prepare data for the PDF
    $data = [
        'records'          => $records,
        'vDate' => $vDate,
        'totalKm'          => $totalKm,
        'totalExpenses'    => $totalExpenses,
        'totalWalletBalance' => $totalWalletBalance,
        'totalApprovedExp' => $totalApprovedExp,
        'totalPendingExp'  => $totalPendingExp,
        'totalRequests'    => $totalRequests,
        'totalCars'        => $totalCars,
        'totalBikes'       => $totalBikes,
        'chartBase64'      => $chartBase64,

    ];

    // Generate the PDF
    $pdf = Pdf::loadView('pdf.expenses_report', $data);

    // Download the PDF
    return response()->streamDownload(function () use ($pdf) {
        echo $pdf->stream();
    }, 'expenses_report.pdf');
}




    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery(); // Get the default query

        $user = auth()->user();

        // Allow all reports for admin and accounts_head roles
        if ($user->roles()->whereIn('name', ['admin'])->exists()) {
            return $query;
        }

        // Show reports for the logged-in user's company for sales_operation role
        if ($user->roles()->where('name', ['sales_operation_head' ,'head' , 'sales_operation'])->exists()) {
            return $query->where('company_id', $user->company_id);
        }

        // Fetch subordinate user IDs for other roles
        $subordinateIds = $user->getAllSubordinateIds();

        // Show only reports for the subordinates
        return $query->whereIn('user_id', $subordinateIds);
    }
  
    
}
