<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminStatsOverview;
use App\Filament\Widgets\TasksDataLineChart;
use App\Filament\Widgets\TasksProgressPieChart;
use App\Filament\Widgets\TeamPerformanceTableWidget;
use App\Filament\Widgets\TeamScheduleMatrixWidget;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getHeading(): string
    {
        return 'Dashboard';
    }

    public function getSubheading(): ?string
    {
        return "Welcome back, ".(auth()->user()?->name ?? 'Admin')."! Here’s what’s happening today.";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addTask')
                ->label('Add Task')
                ->icon('heroicon-m-plus')
                ->color('primary')
                ->url('/admin/tasks/create'),
        ];
    }

    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,
            'md' => 2,
            'lg' => 2,
            'xl' => 2,
        ];
    }

    public function getWidgets(): array
    {
        return [
            AdminStatsOverview::class,
            TeamScheduleMatrixWidget::class,
            // TasksProgressPieChart::class,
            // TasksDataLineChart::class,
            // TeamPerformanceTableWidget::class,
        ];
    }
}
