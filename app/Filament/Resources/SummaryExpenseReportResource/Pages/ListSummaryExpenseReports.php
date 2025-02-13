<?php

        namespace App\Filament\Resources\SummaryExpenseReportResource\Pages;


        use Filament\Actions;
        use Barryvdh\DomPDF\Facade\Pdf;
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Facades\Log;
        use Filament\Resources\Pages\ListRecords;
        use Illuminate\Database\Eloquent\Builder;
        use App\Filament\Resources\SummaryExpenseReportResource;
use App\Models\TrainerVisit;
use Illuminate\Support\Facades\Response as ResponseFacade;

        class ListSummaryExpenseReports extends ListRecords
        {
            protected static string $resource = SummaryExpenseReportResource::class;

            protected function getHeaderActions(): array
            {
                return [

                    Actions\Action::make('total_requests')
                    ->label(function () {
                        // Get the base query for TrainerVisit
                        $query = TrainerVisit::query();
                
                        // Apply filters if available
                        if (method_exists($this, 'applyFiltersToTableQuery')) {
                            $this->applyFiltersToTableQuery($query);
                        }
                
                        // Sum a valid column (replace 'total_expense' with the correct field)
                        $totalRequests = $query->sum('total_expense'); 
                
                        return "Total Requests: ₹" . number_format($totalRequests, 2);
                    })
                    ->color('primary')
                    ->icon('heroicon-o-paper-clip')
                    ->disabled(),
                

                 // Make it a display-only label
                //     Actions\Action::make('total_cash_in_hand')
                //     ->label(function () {
                //         $query = $this->getTableQuery();

                //         if (method_exists($this, 'applyFiltersToTableQuery')) {
                //             $this->applyFiltersToTableQuery($query);
                //         }

                //         $totalCashInHand = $query
                //             ->join('users', 'trainer_visits.user_id', '=', 'users.id')
                //             ->whereDate('trainer_visits.created_at', '>=', now()->subMonth()) // ✅ Explicitly reference trainer_visits.created_at
                //             ->sum('users.wallet_balance');

                //         return "T.Cash in Hand: ₹" . number_format($totalCashInHand, 2);
                //     })
                //     ->color('success')
                //     ->icon('heroicon-o-currency-rupee')
                //     ->disabled(), // Display-only action

                //     // ✅ Display Total Requests dynamically
                // Actions\Action::make('total_requests')
                // ->label(function () {
                //     $query = $this->getTableQuery();

                //     if (method_exists($this, 'applyFiltersToTableQuery')) {
                //         $this->applyFiltersToTableQuery($query);
                //     }

                //     // Compute Total Requests Correctly
                //     $totalRequests = $query->count();  // ✅ Correct count query

                //     return "T.Requests: " . number_format($totalRequests);
                // })
                // ->color('info')
                // ->icon('heroicon-o-calculator')
                // ->disabled(),


                // // ✅ Display Total Expense dynamically
                // Actions\Action::make('total_expense')
                //     ->label(function () {
                //         $query = $this->getTableQuery();

                //         if (method_exists($this, 'applyFiltersToTableQuery')) {
                //             $this->applyFiltersToTableQuery($query);
                //         }

                //         // Compute Total Expense Correctly
                //         $totalExpense = $query->sum('trainer_visits.total_expense');  // ✅ Correct SUM query

                //         return "T.Expense: ₹" . number_format($totalExpense, 2);
                //     })
                //     ->color('warning')
                //     ->icon('heroicon-o-currency-rupee')
                //     ->disabled(),

                //     Actions\Action::make('total_travel_expense')
                //     ->label(function () {
                //         $query = $this->getTableQuery();

                //         if (method_exists($this, 'applyFiltersToTableQuery')) {
                //             $this->applyFiltersToTableQuery($query);
                //         }

                //         $totalTravelExpense = $query->sum('trainer_visits.travel_expense');

                //         return "T.Travel Expense: ₹" . number_format($totalTravelExpense, 2);
                //     })
                //     ->color('primary')
                //     ->icon('heroicon-o-map')
                //     ->disabled(),

                // Actions\Action::make('total_food_expense')
                //     ->label(function () {
                //         $query = $this->getTableQuery();

                //         if (method_exists($this, 'applyFiltersToTableQuery')) {
                //             $this->applyFiltersToTableQuery($query);
                //         }

                //         $totalFoodExpense = $query->sum('trainer_visits.food_expense');

                //         return "T.Food Expense: ₹" . number_format($totalFoodExpense, 2);
                //     })
                //     ->color('red')
                //     ->icon('heroicon-o-fire')
                //     ->disabled(),

                // Actions\Action::make('total_extra_expense')
                //     ->label(function () {
                //         $query = $this->getTableQuery();

                //         if (method_exists($this, 'applyFiltersToTableQuery')) {
                //             $this->applyFiltersToTableQuery($query);
                //         }

                //         $totalExtraExpense = $query->where('trainer_visits.travel_type', 'extra_expense')
                //             ->sum('trainer_visits.total_expense');

                //         return "T.Extra Expense: ₹" . number_format($totalExtraExpense, 2);
                //     })
                //     ->color('purple')
                //     ->icon('heroicon-o-plus')
                //     ->disabled(),

                // Actions\Action::make('total_verified_expense')
                //     ->label(function () {
                //         $query = $this->getTableQuery();

                //         if (method_exists($this, 'applyFiltersToTableQuery')) {
                //             $this->applyFiltersToTableQuery($query);
                //         }

                //         $totalVerifiedExpense = $query->where('trainer_visits.verify_status', 'verified')
                //             ->sum('trainer_visits.total_expense');

                //         return "T.Verified Expense: ₹" . number_format($totalVerifiedExpense, 2);
                //     })
                //     ->color('green')
                //     ->icon('heroicon-o-check-circle')
                //     ->disabled(),

                // Actions\Action::make('total_approved_expense')
                //     ->label(function () {
                //         $query = $this->getTableQuery();

                //         if (method_exists($this, 'applyFiltersToTableQuery')) {
                //             $this->applyFiltersToTableQuery($query);
                //         }

                //         $totalApprovedExpense = $query->where('trainer_visits.approval_status', 'approved')
                //             ->sum('trainer_visits.total_expense');

                //         return "T.Approved Expense: ₹" . number_format($totalApprovedExpense, 2);
                //     })
                //     ->color('blue')
                //     ->icon('heroicon-o-check')
                //     ->disabled(),

                // Actions\Action::make('total_average_expense')
                //     ->label(function () {
                //         $query = $this->getTableQuery();

                //         if (method_exists($this, 'applyFiltersToTableQuery')) {
                //             $this->applyFiltersToTableQuery($query);
                //         }

                //         $totalAverageExpense = $query->avg('trainer_visits.total_expense');

                //         return "T.Average Expense: ₹" . number_format($totalAverageExpense, 2);
                //     })
                //     ->color('gray')
                //     ->icon('heroicon-o-chart-bar')
                //     ->disabled(),


                Actions\Action::make('download_pdf')
            ->label('Current Table')
            ->color('success')
            ->icon('heroicon-o-arrow-down-tray')
            ->action(fn () => $this->downloadTableData()) // ✅ Ensure proper action binding
            ->requiresConfirmation()
            ->modalHeading('Download Table Data')
            ->modalSubheading('This will download the table with all visible data, respecting the applied filters.'),
                ];
            }


            protected function getTableQuery(): Builder
            {
                // Start with the default query
                $query = parent::getTableQuery();
                $user = auth()->user();
            
                // 1) First, ensure the list only includes users who have EITHER the 'bda' or 'bdm' role.
                $query->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['bda', 'bdm' , 'zonal_manager' , 'regional_manager' , 'head' , 'sales_operation_head',]);
                });
            
                // 2) If the logged-in user is Admin or Sales Operation Head:
                //    - They can see all bda/bdm, but exclude admin users if you still want.
                if ($user->roles()->whereIn('name', ['admin','sales_operation_head'])->exists()) {
                    return $query->whereDoesntHave('roles', function ($q) {
                        $q->where('name', 'admin');
                    });
                }
            
                // 3) Otherwise, only show subordinate users (with bda/bdm) and exclude admin.
                $subordinateIds = $user->getAllSubordinateIds();
            
                return $query
                    ->whereIn('id', $subordinateIds)
                    ->whereDoesntHave('roles', function ($q) {
                        $q->where('name', 'admin');
                    });
            }
            

            // Add this method to handle the download action
            public function downloadTableData()
        {
            try {
                // Fetch the filtered data
                $query = $this->getTableQuery();
                $data = $query->with('user')->get();

                // Helper functions for encoding and sanitization
                $encode = fn ($value) => is_string($value) ? mb_convert_encoding($value, 'UTF-8', 'UTF-8') : $value;
                $sanitize = fn ($value) => preg_replace('/[^\x20-\x7E\xA0-\xFF]/', '', $value);
                $formatCurrency = fn ($value) => $encode(number_format((float) $value, 2) . ' ₹');

                // Format the data for the PDF
                $formattedData = $data->map(fn ($row) => [
                    'User Name' => $encode($sanitize($row->user->name ?? 'N/A')),
                    'Cash in Hand' => $formatCurrency($row->user->wallet_balance ?? 0),
                    'Total Requests' => $encode($sanitize((string) ($row->total_requests ?? 0))),
                    'Total Expense' => $formatCurrency($row->total_expense ?? 0),
                    'Total Travel Expense' => $formatCurrency($row->total_travel_expense ?? 0),
                    'Total Food Expense' => $formatCurrency($row->total_food_expense ?? 0),
                    'Total Extra Expense' => $formatCurrency($row->total_extra_expense ?? 0),
                    'Verified Expense' => $formatCurrency($row->verified_expense ?? 0),
                    'Approved Expense' => $formatCurrency($row->approved_expense ?? 0),
                    'Average Expense' => $formatCurrency($row->average_expense ?? 0),
                ])->toArray();

                // Generate the PDF
                $pdf = Pdf::loadView('pdf.summary_expense_report', ['data' => $formattedData]);

                // Filename for the PDF
                $filename = 'summary_expense_report_' . now()->format('Y-m-d_H-i-s') . '.pdf';

                // Return the PDF as a download
                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, $filename);

            } catch (\Exception $e) {
                // Log error for debugging
                Log::error('PDF Generation Error: ' . $e->getMessage());
                Log::error($e->getTraceAsString());

                return response()->json(['error' => 'Failed to generate PDF. Please try again.'], 500);
            }
        }


        


        }
