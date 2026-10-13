<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('ALCO Associate Solutions')
            ->brandLogo(fn () => view('filament.admin.brand-logo'))
            ->darkModeBrandLogo(fn () => view('filament.admin.brand-logo', ['color' => '#ffffff']))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('brand/alco-favicon.svg'))
            ->font('Instrument Sans')
            ->colors([
                // Explicit palettes: Color::hex() regenerates shades on a fixed
                // lightness curve, which turns the brand navy into a bright blue.
                // Values mirror the navy/aqua tokens in resources/css/app.css,
                // shifted so Filament's button shade (600) is a deep navy.
                'primary' => [
                    50 => '#f4f8fc',
                    100 => '#e8eef6',
                    200 => '#d5dfeb',
                    300 => '#a9c0dc',
                    400 => '#83a8d3',
                    500 => '#335d8c',
                    600 => '#1f4570',
                    700 => '#12365d',
                    800 => '#062849',
                    900 => '#041a32',
                    950 => '#020e1e',
                ],
                'info' => [
                    50 => '#ebfafe',
                    100 => '#d4f4fc',
                    200 => '#abecfb',
                    300 => '#7fe3f8',
                    400 => '#52d7f0',
                    500 => '#1cb2d2',
                    600 => '#178bae',
                    700 => '#0d6f8e',
                    800 => '#0d5873',
                    900 => '#0f4358',
                    950 => '#072b3c',
                ],
                'gray' => Color::Slate,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
