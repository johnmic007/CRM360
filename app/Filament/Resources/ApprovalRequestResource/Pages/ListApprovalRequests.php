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
        if ($user->hasRole(['admin', 'sales'])) {
            return ApprovalRequest::query()
                ->whereHas('user', function ($query) use ($user) {
                    $query->where('company_id', $user->company_id);
                });
        }

        // Managers see requests assigned to them
        return ApprovalRequest::query()
            ->where('manager_id', $user->id)
            ->where('company_id', $user->company_id);
    }

    public function getTabs(): array
    {
        return [
            Tab::make('all')
                ->label('All Requests')
                ->query(fn (Builder $query) => $query) // Show all requests
                ->badge(fn () => ApprovalRequest::query()->count()),

            Tab::make('pending')
                ->label('Pending Requests')
                ->query(fn (Builder $query) => $query->where('status', 'Pending'))
                ->badge(fn () => ApprovalRequest::query()->where('status', 'Pending')->count()),

            Tab::make('approved')
                ->label('Approved Requests')
                ->query(fn (Builder $query) => $query->where('status', 'Approved'))
                ->badge(fn () => ApprovalRequest::query()->where('status', 'Approved')->count()),

            Tab::make('rejected')
                ->label('Rejected Requests')
                ->query(fn (Builder $query) => $query->where('status', 'Rejected'))
                ->badge(fn () => ApprovalRequest::query()->where('status', 'Rejected')->count()),
        ];
    }
}
