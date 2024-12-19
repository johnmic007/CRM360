<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->when(
                !auth()->user()->hasRole(['admin' , 'head_trainer']),
                fn (Builder $query) => $query->where('user_id', auth()->id()) // Non-admins see only their tasks
            )
            ->when(
                auth()->user()->hasRole(['admin', 'sales' , 'head_trainer']), // Admins and Sales see all tasks for their company
                fn (Builder $query) => $query->where('company_id', auth()->user()->company_id) // Admins see tasks for their company
            );
    }

    public function getTabs(): array
    {
        $userId = auth()->id();
        $companyId = auth()->user()->company_id;
        $isAdmin = auth()->user()->hasRole(['admin', 'head_trainer']);

        return [
            'all' => Tab::make('All Tasks')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->when(
                        !$isAdmin,
                        fn ($q) => $q->where('user_id', $userId), // Non-admins see their tasks
                        fn ($q) => $q->where('company_id', $companyId) // Admins see all company tasks
                    )
                ),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->where('status', 'pending')
                          ->when(
                              !$isAdmin,
                              fn ($q) => $q->where('user_id', $userId), // Non-admins see their tasks
                              fn ($q) => $q->where('company_id', $companyId) // Admins see all company tasks
                          )
                )
                ->badge(fn () => $this->getTaskStatusCount('pending', $userId, $companyId, $isAdmin)),

            'in_progress' => Tab::make('In Progress')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->where('status', 'in_progress')
                          ->when(
                              !$isAdmin,
                              fn ($q) => $q->where('user_id', $userId), // Non-admins see their tasks
                              fn ($q) => $q->where('company_id', $companyId) // Admins see all company tasks
                          )
                )
                ->badge(fn () => $this->getTaskStatusCount('in_progress', $userId, $companyId, $isAdmin)),

            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->where('status', 'completed')
                          ->when(
                              !$isAdmin,
                              fn ($q) => $q->where('user_id', $userId), // Non-admins see their tasks
                              fn ($q) => $q->where('company_id', $companyId) // Admins see all company tasks
                          )
                )
                ->badge(fn () => $this->getTaskStatusCount('completed', $userId, $companyId, $isAdmin)),

            'due' => Tab::make('Due')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereDate('end_date', '<', now())
                          ->whereIn('status', ['pending', 'in_progress'])
                          ->when(
                              !$isAdmin,
                              fn ($q) => $q->where('user_id', $userId), // Non-admins see their tasks
                              fn ($q) => $q->where('company_id', $companyId) // Admins see all company tasks
                          )
                )
                ->badge(fn () => $this->getDueTaskCount($userId, $companyId, $isAdmin)),
        ];
    }

    protected function getTaskStatusCount(string $status, int $userId, int $companyId, bool $isAdmin): int
    {
        return TaskResource::getEloquentQuery()
            ->where('status', $status)
            ->when(
                !$isAdmin,
                fn ($query) => $query->where('user_id', $userId), // Non-admins see their tasks
                fn ($query) => $query->where('company_id', $companyId) // Admins see all company tasks
            )
            ->count();
    }

    protected function getDueTaskCount(int $userId, int $companyId, bool $isAdmin): int
    {
        return TaskResource::getEloquentQuery()
            ->whereDate('end_date', '<', now())
            ->whereIn('status', ['pending', 'in_progress'])
            ->when(
                !$isAdmin,
                fn ($query) => $query->where('user_id', $userId), // Non-admins see their tasks
                fn ($query) => $query->where('company_id', $companyId) // Admins see all company tasks
            )
            ->count();
    }
}
