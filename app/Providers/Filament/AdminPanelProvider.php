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

                        /* Compact dashboard summary cards */
                        .fi-wi-stats-overview-stats-ctn {
                            gap: 1rem !important;
                        }
                        .dashboard-stat-card {
                            min-height: 7rem;
                            padding: 1rem 1.1rem !important;
                        }
                        .dashboard-stat-card .fi-wi-stats-overview-stat-value {
                            font-size: 1.7rem !important;
                            line-height: 1.1 !important;
                        }
                        .dashboard-stat-card .fi-wi-stats-overview-stat-label,
                        .dashboard-stat-card .fi-wi-stats-overview-stat-description {
                            font-size: .8rem !important;
                        }
                        .dashboard-stat-card .fi-wi-stats-overview-stat-icon {
                            border-radius: .55rem;
                            height: 2rem !important;
                            padding: .42rem;
                            width: 2rem !important;
                        }
                        .dashboard-stat-employees .fi-wi-stats-overview-stat-icon { background: #ede9fe; color: #7c3aed !important; }
                        .dashboard-stat-bpovo .fi-wi-stats-overview-stat-icon { background: #dcfce7; color: #16a34a !important; }
                        .dashboard-stat-gitmit .fi-wi-stats-overview-stat-icon { background: #dbeafe; color: #2563eb !important; }

                        @media (max-width: 640px) {
                            /* Put page actions beside the title instead of below it on phones. */
                            .fi-header {
                                align-items: center !important;
                                flex-direction: row !important;
                                justify-content: space-between !important;
                            }
                            .fi-header > div:last-child {
                                margin-left: auto;
                            }

                            /* Hide the welcome sentence under the Dashboard heading on phones. */
                            .fi-header-subheading {
                                display: none !important;
                            }

                            /* Keep the three dashboard summaries together on phone screens. */
                            .fi-wi-stats-overview .fi-wi-stats-overview-stats-ctn {
                                display: grid !important;
                                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
                                gap: .45rem !important;
                            }
                            .dashboard-stat-card {
                                min-height: 4rem;
                                padding: .6rem !important;
                            }
                            .dashboard-stat-card .fi-wi-stats-overview-stat-value {
                                font-size: 1.1rem !important;
                            }
                            .dashboard-stat-card .fi-wi-stats-overview-stat-label,
                            .dashboard-stat-card .fi-wi-stats-overview-stat-description {
                                font-size: .55rem !important;
                                line-height: 1.15 !important;
                            }
                            .dashboard-stat-card .fi-wi-stats-overview-stat-icon {
                                height: 1.4rem !important;
                                padding: .25rem;
                                width: 1.4rem !important;
                            }
                        }
                    </style>
                ')
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                function (): HtmlString {
                    $choice = session()->pull('task_notification_choice');

                    if (! is_array($choice)) {
                        return new HtmlString('');
                    }

                    $choiceJson = json_encode($choice, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

                    return new HtmlString(<<<HTML
                        <style>
                            .task-notification-overlay { align-items: center; background: rgba(15, 23, 42, .48); display: flex; inset: 0; justify-content: center; padding: 1rem; position: fixed; z-index: 10000; }
                            .task-notification-modal { background: #fff; border-radius: 1.25rem; box-shadow: 0 25px 60px rgba(15, 23, 42, .3); color: #0f172a; max-width: 28rem; padding: 2rem; position: relative; text-align: center; width: 100%; }
                            .task-notification-close { background: transparent; border: 0; color: #94a3b8; cursor: pointer; font-size: 1.75rem; line-height: 1; position: absolute; right: 1rem; top: .8rem; }
                            .task-notification-check { align-items: center; border: 2px solid #22c55e; border-radius: 9999px; color: #22c55e; display: flex; font-size: 2rem; height: 5.25rem; justify-content: center; margin: .25rem auto 1.5rem; width: 5.25rem; }
                            .task-notification-title { font-size: 1.75rem; font-weight: 800; letter-spacing: -.025em; line-height: 1.2; margin: 0; }
                            .task-notification-subtitle { color: #64748b; margin: .75rem 0 1.5rem; }
                            .task-notification-options { display: grid; gap: .75rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
                            .task-notification-option, .task-notification-skip { align-items: center; background: #fff; border: 1px solid #dbe3ef; border-radius: .75rem; color: #0f172a; cursor: pointer; display: flex; font: inherit; gap: .65rem; justify-content: flex-start; padding: 1rem; text-align: left; text-decoration: none; transition: border-color .15s, box-shadow .15s, transform .15s; }
                            .task-notification-option:hover { border-color: #22c55e; box-shadow: 0 8px 20px rgba(34, 197, 94, .13); transform: translateY(-1px); }
                            .task-notification-option[aria-disabled="true"] { cursor: not-allowed; opacity: .55; pointer-events: none; }
                            .task-notification-icon { color: #16a34a; flex: 0 0 auto; font-size: 1.55rem; }
                            .task-notification-option strong { display: block; font-size: .92rem; }
                            .task-notification-option small { color: #64748b; display: block; font-size: .75rem; margin-top: .1rem; }
                            .task-notification-skip { color: #475569; justify-content: center; margin-top: 1rem; padding: .8rem 1rem; text-align: center; width: 100%; }
                            .task-notification-skip:hover { background: #f8fafc; }
                            @media (max-width: 420px) { .task-notification-modal { border-radius: 1rem; padding: 1.5rem 1rem; } .task-notification-options { grid-template-columns: 1fr; } .task-notification-title { font-size: 1.5rem; } }
                        </style>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const data = {$choiceJson};
                                const overlay = document.createElement('div');
                                overlay.className = 'task-notification-overlay';
                                overlay.innerHTML = '<section class="task-notification-modal" role="dialog" aria-modal="true" aria-labelledby="task-notification-title">' +
                                    '<button class="task-notification-close" type="button" aria-label="Close">×</button>' +
                                    '<div class="task-notification-check">✓</div>' +
                                    '<h2 class="task-notification-title" id="task-notification-title">Task created<br>successfully!</h2>' +
                                    '<p class="task-notification-subtitle"></p>' +
                                    '<div class="task-notification-options">' +
                                        '<a class="task-notification-option task-notification-email"><span class="task-notification-icon">✉</span><span><strong>Send Email</strong><small>Notify via email</small></span></a>' +
                                        '<a class="task-notification-option task-notification-whatsapp" target="_blank" rel="noopener"><span class="task-notification-icon">◉</span><span><strong>Send WhatsApp</strong><small>Notify via WhatsApp</small></span></a>' +
                                    '</div>' +
                                    '<button class="task-notification-skip" type="button">Don’t send a notification</button>' +
                                '</section>';

                                const close = () => overlay.remove();
                                overlay.querySelector('.task-notification-subtitle').textContent = 'How would you like to notify ' + data.employeeName + '?';
                                overlay.querySelector('.task-notification-email').href = data.emailUrl;
                                overlay.querySelector('.task-notification-email').addEventListener('click', close);

                                const whatsApp = overlay.querySelector('.task-notification-whatsapp');
                                if (data.whatsAppUrl) {
                                    whatsApp.href = data.whatsAppUrl;
                                    whatsApp.addEventListener('click', close);
                                } else {
                                    whatsApp.setAttribute('aria-disabled', 'true');
                                    whatsApp.querySelector('small').textContent = 'Add employee WhatsApp number first';
                                }

                                overlay.querySelector('.task-notification-close').addEventListener('click', close);
                                overlay.querySelector('.task-notification-skip').addEventListener('click', close);
                                overlay.addEventListener('click', (event) => { if (event.target === overlay) close(); });
                                document.body.appendChild(overlay);
                            });
                        </script>
                        HTML);
                }
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
