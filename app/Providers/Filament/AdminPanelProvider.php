<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\CalendarWidget;
use App\Filament\Widgets\DealWonLineChartWidget;
use App\Filament\Widgets\SalesLeadChartWidget;
use App\Filament\Widgets\SubordinateVisitsWidget;
use App\Filament\Widgets\WalletBalanceWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {

        // $widgets = [
        //     WalletBalanceWidget::class,
        //     CalendarWidget::class,
        // ];
        
        // // Conditionally add the DealWonLineChartWidget and SalesLeadChartWidget
        // if (auth()->check() && auth()->user()->hasAnyRole(['admin', 'sales_operation'])) {
        //     $widgets[] = DealWonLineChartWidget::class;
        //     $widgets[] = SalesLeadChartWidget::class;
        // }
        
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                WalletBalanceWidget::class,
                CalendarWidget::class,
                SalesLeadChartWidget::class,
                DealWonLineChartWidget::class,
            ])
            ->databaseNotifications()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            // ->plugin(\TomatoPHP\FilamentInvoices\FilamentInvoicesPlugin::make())
            ->authMiddleware([
                Authenticate::class,
            ]);
            
    }
}
