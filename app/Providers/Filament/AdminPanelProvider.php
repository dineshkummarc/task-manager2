<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Navigation\MenuItem;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\HtmlString;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
    ->default()
    ->id('admin')
    ->path('admin')
    ->brandLogo(asset('images/bpovo_logo.png')) // Yeh line yahan add kar dein
    ->brandLogoHeight('7.5rem')                 // Logo ki height adjust karne ke liye
    ->login()
    ->darkMode(false)
    ->userMenuItems([
        'logout' => MenuItem::make()
            ->label('Logout')
            ->icon('heroicon-m-arrow-left-on-rectangle')
            ->color('danger'),
    ])
            ->colors([
                'primary' => Color::Indigo,
                'gray' => Color::Slate,
            ])
            ->brandName('BPOVO')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString('
                    <style>
                        /* Sidebar ki default width kam karne ke liye */
                        .fi-sidebar {
                            width: 15rem !important; /* Width ko compact kar diya gaya hai */
                            background-color: #d8e7eeff !important; /* Light blue background */
                        }

                        /* Desktop screen par main content ka margin adjust karna */
                        @media (min-width: 1024px) {
                            .fi-main-scaffold {
                                padding-inline-start: 15rem !important;
                            }
                        }

                        /* Sidebar Navigation items styling on hover */
                        .fi-sidebar-nav a:hover,
                        .fi-sidebar-item-button:hover,
                        .fi-sidebar-item a:hover {
                            background-color: #2563eb !important; /* Bright Blue */
                            color: #ffffff !important;
                            border-radius: 0.5rem !important;
                            transition: all 0.2s ease-in-out !important;
                            transform: translateX(4px);
                            position: relative;
                            z-index: 50;
                        }

                        /* Target all child texts and svg icons on hover */
                        .fi-sidebar-nav a:hover *,
                        .fi-sidebar-item-button:hover *,
                        .fi-sidebar-item a:hover * {
                            color: #ffffff !important;
                            fill: currentColor !important;
                        }

                        /* Active item styling */
                        .fi-sidebar-item-active > a,
                        .fi-sidebar-item.fi-active > a,
                        .fi-sidebar-item-button.bg-gray-100 {
                            background-color: #bfdbfe !important;
                            color: #1e3a8a !important;
                            border-radius: 0.5rem !important;
                        }

                        .fi-sidebar-item-active > a *,
                        .fi-sidebar-item.fi-active > a * {
                            color: #1e3a8a !important;
                            fill: currentColor !important;
                        }
                    </style>
                ')
            )
            ->renderHook(
                PanelsRenderHook::USER_MENU_PROFILE_AFTER,
                function (): HtmlString {
                    $user = auth()->user();

                    if (! $user) {
                        return new HtmlString('');
                    }

                    $role = $user->isAdmin() ? 'Admin' : 'Manager';

                    return new HtmlString(
                        '<div class="px-3 pb-3">'
                        . '<div class="flex items-center gap-2 rounded-lg bg-gray-50 px-3 py-2 dark:bg-white/5">'
                        . '<span class="rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">' . e($role) . '</span>'
                        . '<span class="min-w-0 truncate text-xs text-gray-500 dark:text-gray-400">' . e($user->email) . '</span>'
                        . '</div>'
                        . '</div>'
                    );
                }
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\AdminStatsOverview::class,
                \App\Filament\Widgets\TeamScheduleMatrixWidget::class,
                \App\Filament\Widgets\TeamPerformanceTableWidget::class,
                // \App\Filament\Widgets\TodayTasksKanbanWidget::class,
                // \App\Filament\Widgets\WorkspaceFeedWidget::class,
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
