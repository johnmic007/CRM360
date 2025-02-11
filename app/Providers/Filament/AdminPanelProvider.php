<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use App\Filament\Widgets\CalendarWidget;
use Filament\Http\Middleware\Authenticate;
use App\Filament\Widgets\DashboardStatsWidget;
use App\Filament\Widgets\SalesLeadChartWidget;
use Illuminate\Session\Middleware\StartSession;
use App\Filament\Widgets\DealWonLineChartWidget;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Filament\Widgets\SubordinateVisitsWidget;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

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
                CalendarWidget::class,
                SalesLeadChartWidget::class,
                DealWonLineChartWidget::class,
                DashboardStatsWidget::class,
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
