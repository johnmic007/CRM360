<?php

namespace App\Filament\Resources\ApprovalRequestResource\Pages;

use App\Filament\Resources\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListApprovalRequests extends ListRecords
{
    protected static string $resource = ApprovalRequestResource::class;

    public function getTableQuery(): Builder
    {
        $user = Auth::user();
    
        // Admin and Sales roles see all requests for their company_id
        if ($user->hasRole(['admin', 'sales_operation'])) {
            return ApprovalRequest::query()
                ->whereHas('user', function ($query) use ($user) {
                    $query->where('company_id', $user->company_id);
                });
        }
    
        // Managers see requests assigned to them, and users see their own requests
        return ApprovalRequest::query()
            ->where(function ($query) use ($user) {
                $query->where('manager_id', $user->id)
                    ->orWhere('user_id', $user->id); // Include the user's own requests
            })
            ->where('company_id', $user->company_id);
    }
    

    public function getTabs(): array
    {
        $user = Auth::user();

        return [
            Tab::make('all')
                ->label('All Requests')
                ->query(fn (Builder $query) => $query) ,// Show all requests

            Tab::make('pending')
                ->label('Pending Requests')
                ->query(fn (Builder $query) => $query->where('status', 'Pending')),

            Tab::make('approved')
                ->label('Approved Requests')
                ->query(fn (Builder $query) => $query->where('status', 'Approved')),

            Tab::make('rejected')
                ->label('Rejected Requests')
                ->query(fn (Builder $query) => $query->where('status', 'Rejected')),
        ];
    }
}
