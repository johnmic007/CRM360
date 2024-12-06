<?php

namespace App\Providers;

use App\Models\School;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
   
    
    public function boot()
    {
        // FilamentInvoices::registerFor([
        //     InvoiceFor::make(School::class)
        //         ->label('School')
        // ]);
        // FilamentInvoices::registerFrom([
        //     InvoiceFrom::make(School::class)
        //         ->label('Company')
        // ]);
    }
}
