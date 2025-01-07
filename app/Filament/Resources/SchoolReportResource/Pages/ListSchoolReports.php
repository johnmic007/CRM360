<?php

namespace App\Filament\Resources\SchoolReportResource\Pages;

use App\Filament\Resources\SchoolReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;


class ListSchoolReports extends ListRecords
{
    protected static string $resource = SchoolReportResource::class;


    protected function getHeaderActions(): array
{
    return [
        // Total Reports
        Actions\Action::make('total_reports')
            ->label(function () {
                $query = $this->getTableQuery();
                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }
                $totalReports = $query->count();
                return "Total Reports: {$totalReports}";
            })
            ->color('primary')
            ->icon('heroicon-o-document-text')
            ->disabled(), // Display-only action

        // Total Potential Meets
        Actions\Action::make('total_potential_meets')
            ->label(function () {
                $query = $this->getTableQuery();
                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }
                $potentialMeets = $query->where('potential_meet', '>', 0)->count();
                return "Potential Meets: {$potentialMeets}";
            })
            ->color('success')
            ->icon('heroicon-o-hand-raised')
            ->disabled(), // Display-only action

        // Total Follow-Ups Pending
        // Actions\Action::make('follow_ups_pending')
        //     ->label(function () {
        //         $query = $this->getTableQuery();
        //         if (method_exists($this, 'applyFiltersToTableQuery')) {
        //             $this->applyFiltersToTableQuery($query);
        //         }
        //         $followUpsPending = $query->whereDate('follow_up_date', '>=', now())->count();
        //         return "Follow-Ups Pending: {$followUpsPending}";
        //     })
        //     ->color('warning')
        //     ->icon('heroicon-o-clock')
        //     ->disabled(), // Display-only action

        

        // Total Books Issued
        Actions\Action::make('books_issued')
            ->label(function () {
                $query = $this->getTableQuery();
                if (method_exists($this, 'applyFiltersToTableQuery')) {
                    $this->applyFiltersToTableQuery($query);
                }
                $booksIssued = $query->where('is_book_issued', true)->count();
                return "Books Issued: {$booksIssued}";
            })
            ->color('info')
            ->icon('heroicon-o-book-open')
            ->disabled(), // Display-only action
    ];
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
            // return $query->where('company_id', $user->company_id);

            return $query;

        }

        // Fetch subordinate user IDs for other roles
        $subordinateIds = $user->getAllSubordinateIds();

        // Show only reports for the subordinates
        return $query->whereIn('user_id', $subordinateIds);
    }
}
