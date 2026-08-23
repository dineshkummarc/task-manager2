<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use App\Models\TimeLog;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TeamScheduleMatrixWidget extends Widget
{
    protected static string $view = 'filament.widgets.team-schedule-matrix';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public string $viewMode = 'daily';

    public string $selectedDate = '';

    public ?int $selectedUserId = null;

    /** @var 'all'|'pending'|'in_progress'|'unresolved'|'completed' */
    public string $selectedStatus = 'completed';

    public bool $showHistoryModal = false;

    public ?int $historyUserId = null;

    public string $historyUserName = '';

    /** @var array<int, array<string, mixed>> */
    public array $historyTasks = [];

    public function mount(): void
    {
        $this->selectedDate = now()->toDateString();
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['daily', 'weekly', 'monthly'], true)) {
            $this->viewMode = $mode;

            if ($mode !== 'daily') {
                $this->selectedUserId = null;
                $this->closeTaskHistory();
            }
        }
    }

    public function setDate(?string $value): void
    {
        $this->selectedDate = $this->clampDate($value);
    }

    public function setSelectedUser($userId = null): void
    {
        $this->selectedUserId = filled($userId) ? (int) $userId : null;
    }

    public function setSelectedStatus(string $status = 'all'): void
    {
        $this->selectedStatus = in_array($status, ['all', 'pending', 'in_progress', 'unresolved', 'completed'], true)
            ? $status
            : 'all';
    }

    public function viewUserReport(int $userId)
    {
        if (! \App\Filament\Pages\UserTaskHistory::canAccess()) {
            return null;
        }

        $period = match ($this->viewMode) {
            'weekly' => 'weekly',
            'monthly' => 'monthly',
            default => 'today',
        };

        return redirect(\App\Filament\Pages\UserTaskHistory::getUrl().'?user='.$userId.'&period='.$period);
    }

    public function openTaskHistory(int $userId): void
    {
        $user = User::query()->find($userId);

        if (! $user) {
            return;
        }

        $this->historyUserId = $user->id;
        $this->historyUserName = (string) $user->name;
        $this->historyTasks = Task::query()
            ->where(function ($query) use ($user) {
                $query->where('assigned_to', $user->id)
                    ->orWhere('user_id', $user->id);
            })
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => (string) $task->title,
                'description' => trim(html_entity_decode(strip_tags((string) ($task->description ?: '')), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
                'blockage' => $this->blockageText($task),
                'status' => $this->statusColumnKey($task),
                'status_label' => $this->statusLabel($task),
                'estimated_minutes' => $task->estimated_minutes,
                'due_date' => $task->due_date
                    ? $this->inAppTz($task->due_date)->format('M j, Y g:i A')
                    : null,
                'updated_at' => $task->updated_at
                    ? $this->inAppTz($task->updated_at)->format('M j, Y g:i A')
                    : null,
            ])
            ->all();
        $this->showHistoryModal = true;
    }

    public function closeTaskHistory(): void
    {
        $this->showHistoryModal = false;
        $this->historyUserId = null;
        $this->historyUserName = '';
        $this->historyTasks = [];
    }

    protected function getViewData(): array
    {
        $this->selectedDate = $this->clampDate($this->selectedDate);

        $anchor = $this->selectedDay();
        $range = $this->dateRange($anchor);
        $employees = $this->employees();
        $useStatusColumns = $this->viewMode === 'daily' && filled($this->selectedUserId);

        $statuses = collect([
            (object) ['id' => 'pending', 'name' => 'Pending Task'],
            (object) ['id' => 'in_progress', 'name' => 'In Process'],
            (object) ['id' => 'blockage', 'name' => 'Blockage'],
            (object) ['id' => 'completed', 'name' => 'Completed'],
        ]);

        $columns = $useStatusColumns ? $statuses : $employees;

        $rows = $this->buildRows($employees, $columns, $anchor, $range);

        $filterItems = function ($items) {
            return collect($items)
                ->unique('id')
                ->filter(function (array $item): bool {
                if ($item['is_break']) {
                    return false;
                }

                $status = $item['status'] === 'blockage' ? 'unresolved' : $item['status'];

                    return $this->selectedStatus === 'all' || $status === $this->selectedStatus;
                })
                ->values()
                ->all();
        };

        $scheduleRows = collect($rows)
            ->map(fn (array $row) => [
                'label' => $row['label'],
                'items' => $filterItems(collect($row['cells'])->flatten(1)),
            ])
            ->all();

        $scheduleItems = collect($scheduleRows)
            ->flatMap(fn (array $row) => $row['items'])
            ->sortBy(fn (array $item) => $item['time'])
            ->values();

        return [
            'employees' => $employees,
            'columns' => $columns,
            'scheduleRows' => $scheduleRows,
            'scheduleItems' => $scheduleItems,
            'maxDate' => now()->toDateString(),
            'cornerLabel' => match ($this->viewMode) {
                'weekly' => 'Day',
                'monthly' => 'Date',
                default => 'Time',
            },
            'heading' => $this->headingFor($anchor),
            'useStatusColumns' => $useStatusColumns,
        ];
    }

    protected function headingFor(Carbon $anchor): string
    {
        return match ($this->viewMode) {
            'weekly' => "Week's Task Schedule",
            'monthly' => 'Monthly Task Schedule',
            default => $anchor->isToday()
                ? "Today's Task Schedule"
                : $anchor->format('M j, Y') . ' Task Schedule',
        };
    }

    protected function clampDate(?string $value): string
    {
        $today = now()->startOfDay();

        try {
            $date = filled($value) ? Carbon::parse($value)->startOfDay() : $today->copy();
        } catch (\Throwable) {
            $date = $today->copy();
        }

        if ($date->gt($today)) {
            $date = $today->copy();
        }

        return $date->toDateString();
    }

    protected function selectedDay(): Carbon
    {
        try {
            return Carbon::parse($this->selectedDate ?: now())->startOfDay();
        } catch (\Throwable) {
            return now()->startOfDay();
        }
    }

    /**
     * @return array{start: Carbon, end: Carbon}
     */
    protected function dateRange(Carbon $anchor): array
    {
        $todayEnd = now()->endOfDay();

        if ($this->viewMode === 'weekly') {
            $end = $anchor->copy()->endOfWeek(Carbon::SUNDAY);

            return [
                'start' => $anchor->copy()->startOfWeek(Carbon::MONDAY),
                'end' => $end->gt($todayEnd) ? $todayEnd->copy() : $end,
            ];
        }

        if ($this->viewMode === 'monthly') {
            $end = $anchor->copy()->endOfMonth();

            return [
                'start' => $anchor->copy()->startOfMonth(),
                'end' => $end->gt($todayEnd) ? $todayEnd->copy() : $end,
            ];
        }

        return [
            'start' => $anchor->copy()->startOfDay(),
            'end' => $anchor->copy()->endOfDay(),
        ];
    }

    protected function employees(): Collection
    {
        $employees = User::query()
            ->where(function ($query) {
                $query->whereIn('role', ['employee', 'manager'])
                    ->orWhereHas('roles', fn ($roles) => $roles->whereIn('name', ['employee', 'manager']));
            })
            ->orderBy('name')
            ->get();

        return $employees->isNotEmpty()
            ? $employees
            : User::query()->orderBy('name')->get();
    }

    /**
     * @param  array{start: Carbon, end: Carbon}  $range
     * @return array<int, array<string, mixed>>
     */
    protected function buildRows(Collection $employees, Collection $columns, Carbon $anchor, array $range): array
    {
        $placements = $this->buildPlacements($employees, $range['start'], $range['end']);
        $rows = [];

        if ($this->viewMode === 'daily') {
            $cursor = $anchor->copy()->startOfDay();
            $end = $anchor->copy()->endOfDay();

            while ($cursor->lt($end)) {
                $rows[] = $this->makeRow($cursor->format('H:i'), $cursor->format('g:i A'), $columns, $placements);
                $cursor->addHour();
            }

            return $rows;
        }

        $day = $range['start']->copy()->startOfDay();
        $last = $range['end']->copy()->startOfDay();

        while ($day->lte($last)) {
            $label = $this->viewMode === 'weekly'
                ? $day->format('D j')
                : $day->format('M j');

            $rows[] = $this->makeRow($day->toDateString(), $label, $columns, $placements);
            $day->addDay();
        }

        return $rows;
    }

    /**
     * @return array<string, array<int|string, array<int, array<string, mixed>>>>
     */
    protected function buildPlacements(Collection $employees, Carbon $rangeStart, Carbon $rangeEnd): array
    {
        $useStatusColumns = $this->viewMode === 'daily' && filled($this->selectedUserId);
        $employeeIds = $useStatusColumns
            ? collect([(int) $this->selectedUserId])
            : $employees->pluck('id')->map(fn ($id) => (int) $id);
            
        $from = $rangeStart->toDateString();
        $to = $rangeEnd->toDateString();
        $placements = [];

        $logs = TimeLog::query()
            ->with('task')
            ->whereIn('user_id', $employeeIds)
            ->where(function ($query) use ($from, $to) {
                $query->whereRaw('DATE(start_time) between ? and ?', [$from, $to])
                    ->orWhereRaw('DATE(end_time) between ? and ?', [$from, $to]);
            })
            ->get();

        $tasks = Task::query()
            ->with('assignee')
            ->where(function ($query) use ($employeeIds) {
                $query->whereIn('assigned_to', $employeeIds)
                    ->orWhereIn('user_id', $employeeIds);
            })
            ->where(function ($query) use ($from, $to) {
                $query->whereRaw('DATE(due_date) between ? and ?', [$from, $to])
                    ->orWhere(function ($inner) use ($from, $to) {
                        $inner->whereNull('due_date')
                            ->whereRaw('DATE(created_at) between ? and ?', [$from, $to]);
                    });
            })
            ->get()
            ->keyBy('id');

        foreach ($logs as $log) {
            if ($log->task && ! $tasks->has($log->task_id)) {
                $tasks->put($log->task_id, $log->task);
            }
        }

        $loggedTaskIds = [];

        foreach ($logs as $log) {
            $task = $log->task ?? $tasks->get($log->task_id);

            if (! $task || ! $log->start_time) {
                continue;
            }

            $userId = (int) $log->user_id;
            if ($useStatusColumns && $userId !== (int) $this->selectedUserId) {
                continue;
            }

            $columnKey = $useStatusColumns ? $this->statusColumnKey($task) : $userId;

            $loggedTaskIds[$task->id][$userId] = true;
            $rowKey = $this->slotKey($log->start_time);
            $placements[$rowKey][$columnKey][] = $this->taskPayload($task, $log->start_time);
        }

        foreach ($tasks as $task) {
            $userId = (int) ($task->assigned_to ?: $task->user_id);

            if (! $employeeIds->contains($userId)) {
                $userId = (int) $task->user_id;
            }

            if (! $userId || isset($loggedTaskIds[$task->id][$userId])) {
                continue;
            }
            
            if ($useStatusColumns && $userId !== (int) $this->selectedUserId) {
                continue;
            }

            $columnKey = $useStatusColumns ? $this->statusColumnKey($task) : $userId;

            $when = $this->taskClockTime($task);
            $rowKey = $this->slotKey($when);
            $placements[$rowKey][$columnKey][] = $this->taskPayload($task, $when);
        }

        return $placements;
    }

    protected function taskClockTime(Task $task): Carbon
    {
        $due = $task->due_date;
        $created = $task->created_at;

        if ($due) {
            if ($due->format('H:i:s') !== '00:00:00') {
                return $this->inAppTz($due);
            }
            return $this->inAppTz($due)->setTime(10, 0);
        }

        return $this->inAppTz($created ?? now());
    }

    protected function inAppTz(Carbon $when): Carbon
    {
        return $when->copy()->timezone('Asia/Karachi');
    }

    protected function slotKey(Carbon $when): string
    {
        $when = $this->inAppTz($when);

        if ($this->viewMode !== 'daily') {
            return $when->toDateString();
        }

        $hour = (int) $when->format('G');

        return sprintf('%02d:00', $hour);
    }

    /**
     * @param  array<string, array<int|string, array<int, array<string, mixed>>>>  $placements
     * @return array<string, mixed>
     */
    protected function makeRow(string $key, string $label, Collection $columns, array $placements): array
    {
        $cells = [];

        foreach ($columns as $column) {
            $columnId = $column->id;
            $cells[$columnId] = $this->uniqueItems($placements[$key][$columnId] ?? []);
        }

        return [
            'key' => $key,
            'label' => $label,
            'cells' => $cells,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function uniqueItems(array $items): array
    {
        $seen = [];
        $unique = [];

        foreach ($items as $item) {
            $id = $item['id'] ?? null;

            if ($id && isset($seen[$id])) {
                continue;
            }

            if ($id) {
                $seen[$id] = true;
            }

            $unique[] = $item;
        }

        return $unique;
    }

    /**
     * @return array<string, mixed>
     */
    protected function taskPayload(Task $task, Carbon $when): array
    {
        $title = (string) $task->title;
        $isBreak = str_contains(strtolower($title), 'break') || str_contains(strtolower($title), 'lunch');
        $userId = (int) ($task->assigned_to ?: $task->user_id);

        return [
            'id' => $task->id,
            'title' => $title,
            'description' => trim(html_entity_decode(strip_tags((string) $task->description), ENT_QUOTES | ENT_HTML5, 'UTF-8')),
            'started_at' => $task->created_at
                ? $this->inAppTz($task->created_at)->format('M j, Y g:i A')
                : '—',
            'expected_by' => $task->due_date
                ? $this->inAppTz($task->due_date)->format('M j, g:i A')
                : '—',
            'blockage_text' => $this->blockageText($task),
            'estimated_minutes' => $task->estimated_minutes,
            'status' => $this->statusColumnKey($task),
            'is_blocked' => $task->is_blocked || $task->status === 'blockage',
            'time' => $when->format('g:i A'),
            'is_break' => $isBreak,
            'userId' => $userId,
            'userName' => $task->assignee?->name ?? 'Unassigned',
            'tone' => $this->toneFor($task, $isBreak),
        ];
    }

    protected function statusColumnKey(Task $task): string
    {
        if ($task->is_blocked || $task->status === 'blockage') {
            return 'blockage';
        }

        return match ((string) $task->status) {
            'completed' => 'completed',
            'in_progress', 'inprocess', 'in-process' => 'in_progress',
            default => 'pending',
        };
    }

    protected function statusLabel(Task $task): string
    {
        return match ($this->statusColumnKey($task)) {
            'completed' => 'Completed',
            'in_progress' => 'In Process',
            'blockage' => 'Blockage',
            default => 'Pending',
        };
    }

    protected function blockageText(Task $task): string
    {
        return trim((string) (
            $task->block_reason
            ?: $task->blockage_reason
            ?: ($task->is_blocked || $task->status === 'blockage' ? $task->description : '')
        ));
    }

    /**
     * @return array{bg: string, text: string, border: string}
     */
    protected function toneFor(Task $task, bool $isBreak): array
    {
        if ($isBreak) {
            return ['bg' => 'transparent', 'text' => '#64748b', 'border' => 'transparent'];
        }

        if ($task->is_blocked || $task->status === 'blockage') {
            return ['bg' => '#ffe4e6', 'text' => '#9f1239', 'border' => '#fecdd3'];
        }

        return match ($task->status) {
            'completed' => ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#bbf7d0'],
            'in_progress' => ['bg' => '#dbeafe', 'text' => '#1e3a8a', 'border' => '#bfdbfe'],
            default => $this->palette()[$task->id % count($this->palette())],
        };
    }

    /**
     * @return array<int, array{bg: string, text: string, border: string}>
     */
    protected function palette(): array
    {
        return [
            ['bg' => '#fef9c3', 'text' => '#854d0e', 'border' => '#fde68a'],
            ['bg' => '#dbeafe', 'text' => '#1e3a8a', 'border' => '#bfdbfe'],
            ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#bbf7d0'],
            ['bg' => '#ffe4e6', 'text' => '#9f1239', 'border' => '#fecdd3'],
            ['bg' => '#ede9fe', 'text' => '#5b21b6', 'border' => '#ddd6fe'],
        ];
    }
}
