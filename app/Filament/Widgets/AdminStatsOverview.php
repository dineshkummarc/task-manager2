<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        $totalEmployees = User::where('role', 'employee')->orWhereNull('role')->count();

        $bpovoId = Project::where('name', 'BPOVO')->value('id');
        $gitmitId = Project::where('name', 'GITMIT')->value('id');

        $bpovoTasks = $bpovoId ? Task::where('project_id', $bpovoId)->count() : 0;
        $gitmitTasks = $gitmitId ? Task::where('project_id', $gitmitId)->count() : 0;

        return [
            Stat::make('Total Employees', (string) $totalEmployees)
                ->description('View Employees')
                ->descriptionIcon('heroicon-m-arrow-top-right-on-square')
                ->icon('heroicon-m-users')
                ->color('primary')
                ->chart([4, 7, 5, 11, 8, 15, 12])
                ->extraAttributes(['class' => 'dashboard-stat-card dashboard-stat-employees'])
                ->url('/admin/employees'),

            Stat::make('BPOVO Tasks', (string) $bpovoTasks)
                ->description('View Tasks')
                ->descriptionIcon('heroicon-m-briefcase')
                ->icon('heroicon-m-briefcase')
                ->color('success')
                ->chart([5, 8, 6, 10, 7, 13, 16])
                ->extraAttributes(['class' => 'dashboard-stat-card dashboard-stat-bpovo'])
                ->url('/admin/tasks?tableFilters[project_id][value]=' . $bpovoId),

            Stat::make('GITMIT Tasks', (string) $gitmitTasks)
                ->description('View Tasks')
                ->descriptionIcon('heroicon-m-cube')
                ->icon('heroicon-m-cube')
                ->color('info')
                ->chart([3, 6, 4, 9, 5, 10, 12])
                ->extraAttributes(['class' => 'dashboard-stat-card dashboard-stat-gitmit'])
                ->url('/admin/tasks?tableFilters[project_id][value]=' . $gitmitId),
        ];
    }
}
