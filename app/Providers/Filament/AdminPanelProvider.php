<?php

namespace App\Providers\Filament;

use App\Filament\Pages\SalesDashboard;
use App\Filament\Pages\ScooterDashboard;
use App\Filament\Pages\ScootersDashboard;
use App\Filament\Pages\UsersDashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Log;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{

    protected static ?string $authGuard = 'admin';

    public function panel(Panel $panel): Panel
    {

        return $panel
            ->default()
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder
                    ->items([
                        NavigationItem::make('Sales')
                            ->url(fn() => SalesDashboard::getUrl())
                            ->icon('heroicon-o-chart-bar')
                            ->isActiveWhen(fn() => request()->routeIs('filament.admin.pages.sales-dashboard')),
                        NavigationItem::make('Users')
                            ->url(fn() => UsersDashboard::getUrl())
                            ->icon('heroicon-o-user-group')
                            ->isActiveWhen(fn() => request()->routeIs('filament.admin.pages.users-dashboard')),
                        NavigationItem::make('Scooters')
                            ->url(fn() => ScootersDashboard::getUrl())
                            ->icon('heroicon-o-truck')
                            ->isActiveWhen(fn() => request()->routeIs('filament.admin.pages.scooters-dashboard')),
                    ]);
            })
            ->id('admin')
            ->path('admin')
            ->login()
            ->authGuard('admin')
            ->colors([
                'primary' => Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
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
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
