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
    

 protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery(); // Get the default query

        $user = auth()->user();

        if ($user->roles()->whereIn('name', ['admin'])->exists()) {
            return $query;
        }

        if ($user->roles()->where('name', ['sales_operation_head' ,'head' , 'sales_operation'])->exists()) {
            return $query->where('company_id', $user->company_id);
        }

        if ($user->roles()->where('name', ['bda' ,'bdm' ])->exists()) {
            return $query->where('user_id', $user->id);
        }



        $subordinateIds = $user->getAllSubordinateIds();

        return $query->whereIn('user_id', $subordinateIds);
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
