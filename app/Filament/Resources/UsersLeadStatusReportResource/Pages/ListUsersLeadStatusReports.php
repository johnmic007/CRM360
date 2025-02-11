<?php

namespace App\Filament\Resources\UsersLeadStatusReportResource\Pages;

use App\Filament\Resources\UsersLeadStatusReportResource;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;
use App\Models\SalesLeadStatus;

class ListUsersLeadStatusReports extends ListRecords
{
    protected static string $resource = UsersLeadStatusReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('total_visits')
                ->label(function () {
                    $query = $this->getTableQuery();
                    if (method_exists($this, 'applyFiltersToTableQuery')) {
                        $this->applyFiltersToTableQuery($query);
                    }
                    $totalVisits = $query->count();
                    return "Total Visits: {$totalVisits}";
                })
                ->color('primary')
                ->icon('heroicon-o-paper-clip')
                ->disabled(),
                Actions\Action::make('total_school_nurturing')
                // ->label(fn () => "School Nurturing: " . $this->getTableQuery()->where('status', 'School Nurturing')->count())
                ->label(function () {
                    $query = $this->getTableQuery();
                    if (method_exists($this, 'applyFiltersToTableQuery')) {
                        $this->applyFiltersToTableQuery($query);
                    }
                    $totalDealsWon = $query->where('status', 'School Nurturing')->count();
                    return "School Nurturing: {$totalDealsWon}";
                })
                ->color('gray')
                ->icon('heroicon-o-academic-cap')
                ->disabled(),

                Actions\Action::make('total_demo_reschedule')
                ->label(fn () => "Demo Reschedule: " . $this->getTableQuery()->where('status', 'Demo Reschedule')->count())
                ->label(function () {
                    $query = $this->getTableQuery();
                    if (method_exists($this, 'applyFiltersToTableQuery')) {
                        $this->applyFiltersToTableQuery($query);
                    }
                    $totalDealsWon = $query->where('status', 'Demo Reschedule')->count();
                    return "Demo Scheduled: {$totalDealsWon}";
                })
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                ->disabled(),

                Actions\Action::make('total_demo_reschedule')
                // ->label(fn () => "Demo Completed: " . $this->getTableQuery()->where('status', 'Demo Reschedule')->count())
                ->label(function () {
                    $query = $this->getTableQuery();
                    if (method_exists($this, 'applyFiltersToTableQuery')) {
                        $this->applyFiltersToTableQuery($query);
                    }
                    $totalDealsWon = $query->where('status', 'Demo Completed')->count();
                    return "Demo Completed: {$totalDealsWon}";
                })
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                ->disabled(),

            

            Actions\Action::make('total_deals_won')
                ->label(function () {
                    $query = $this->getTableQuery();
                    if (method_exists($this, 'applyFiltersToTableQuery')) {
                        $this->applyFiltersToTableQuery($query);
                    }
                    $totalDealsWon = $query->where('status', 'deal_won')->count();
                    return "Deals Won: {$totalDealsWon}";
                })
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->disabled(),

            Actions\Action::make('total_deals_lost')
                ->label(function () {
                    $query = $this->getTableQuery();
                    if (method_exists($this, 'applyFiltersToTableQuery')) {
                        $this->applyFiltersToTableQuery($query);
                    }
                    $totalDealsLost = $query->where('status', 'deal_lost')->count();
                    return "Deals Lost: {$totalDealsLost}";
                })
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->disabled(),

            // Actions\Action::make('download_pdf')
            //     ->label('PDF')
            //     ->color('success')
            //     ->icon('heroicon-o-arrow-down-tray')
            //     ->action('downloadPDF'),
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

        // Calculate totals
        $totalVisits = $records->count();
        $totalDealsWon = $records->where('status', 'deal_won')->count();
        $totalDealsLost = $records->where('status', 'deal_lost')->count();
        $totalDemoCompleted = $records->where('status', 'Demo Completed')->count();
        $totalDemoReschedule = $records->where('status', 'Demo Reschedule')->count();
        $totalSchoolNurturing = $records->where('status', 'School Nurturing')->count();

        // Group by visit date for the chart
        $groupedByDate = $records->groupBy(function ($record) {
            return $record->visited_date->format('Y-m-d');
        });

        $labels = [];
        $dataTotalVisits = [];
        $dataDealsWon = [];
        $dataDealsLost = [];

        foreach ($groupedByDate as $date => $items) {
            $labels[] = $date;
            $dataTotalVisits[] = $items->count();
            $dataDealsWon[] = $items->where('status', 'deal_won')->count();
            $dataDealsLost[] = $items->where('status', 'deal_lost')->count();
        }

        $chartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Total Visits',
                        'data' => $dataTotalVisits,
                        'backgroundColor' => '#3498db',
                    ],
                    [
                        'label' => 'Deals Won',
                        'data' => $dataDealsWon,
                        'backgroundColor' => '#2ecc71',
                    ],
                    [
                        'label' => 'Deals Lost',
                        'data' => $dataDealsLost,
                        'backgroundColor' => '#e74c3c',
                    ],
                ],
            ],
            'options' => [
                'title' => [
                    'display' => true,
                    'text' => 'Lead Status Trends',
                    'fontSize' => 16,
                ],
                'scales' => [
                    'yAxes' => [
                        ['ticks' => ['beginAtZero' => true]],
                    ],
                ],
            ],
        ];

        // Build the QuickChart URL
        $chartUrl = 'https://quickchart.io/chart?c=' . urlencode(json_encode($chartConfig));
        $response = Http::get($chartUrl);
        $chartBase64 = base64_encode($response->body());

        // Prepare data for the PDF
        $data = [
            'records' => $records,
            'totalVisits' => $totalVisits,
            'totalDealsWon' => $totalDealsWon,
            'totalDealsLost' => $totalDealsLost,
            'totalDemoCompleted' => $totalDemoCompleted,
            'totalDemoReschedule' => $totalDemoReschedule,
            'totalSchoolNurturing' => $totalSchoolNurturing,
            'chartBase64' => $chartBase64,
        ];

        // Generate the PDF
        $pdf = Pdf::loadView('pdf.users_lead_status_report', $data);

        // Download the PDF
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'users_lead_status_report.pdf');
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery(); // Get the default query

        $user = auth()->user();

        // Allow all reports for admin role
        if ($user->roles()->whereIn('name', ['admin','sales_operation_head'])->exists()) {
            return $query;
        }

        // Show reports for the logged-in user's company for specific roles
        // if ($user->roles()->whereIn('name', ['sales_operation_head', 'head', 'sales_operation', 'company'])->exists()) {
        //     return $query->where('company_id', $user->company_id);
        // }

        // Fetch subordinate user IDs for other roles
        $subordinateIds = $user->getAllSubordinateIds();

        // Show only reports for the subordinates
        return $query->whereIn('created_by', $subordinateIds);
    }
}
