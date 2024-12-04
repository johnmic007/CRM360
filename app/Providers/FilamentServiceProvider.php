<?php

namespace App\Providers;

use App\Filament\Pages\SalesLeadKanbanBoard;
use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Register custom Filament pages
        Filament::registerPages([
            SalesLeadKanbanBoard::class,
        ]);
    }
}
