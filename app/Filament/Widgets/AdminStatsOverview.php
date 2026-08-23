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
                ->color('primary')
                ->url('/admin/employees'),

            Stat::make('BPOVO Tasks', (string) $bpovoTasks)
                ->description('View Tasks')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('success')
                ->url('/admin/tasks?tableFilters[project_id][value]=' . $bpovoId),

            Stat::make('GITMIT Tasks', (string) $gitmitTasks)
                ->description('View Tasks')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info')
                ->url('/admin/tasks?tableFilters[project_id][value]=' . $gitmitId),
        ];
    }
}
