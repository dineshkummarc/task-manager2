<?php

namespace App\Filament\Pages;

use App\Models\DailyReportEntry;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\WithPagination;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DailyReports extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Daily Reports';

    protected static ?string $title = 'Daily Reports';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'daily-reports';

    protected static string $view = 'filament.pages.daily-reports';

    public string $selectedDate = '';

    public string $selectedUserId = '';

    public string $selectedProjectId = '';

    public array $expandedUsers = [];

    public function mount(): void
    {
        $this->selectedDate = today('Asia/Karachi')->toDateString();
    }

    public static function canAccess(): bool
    {
        return (bool) (auth()->user()?->isAdmin() || auth()->user()?->isManager());
    }

    public function updatedSelectedDate(): void
    {
        $this->expandedUsers = [];
    }

    public function updatedSelectedUserId(): void
    {
        $this->expandedUsers = [];
    }

    public function updatedSelectedProjectId(): void
    {
        $this->expandedUsers = [];
    }

    public function toggleUser(int $userId): void
    {
        $this->expandedUsers[$userId] = ! ($this->expandedUsers[$userId] ?? false);
    }

    public function getUsers(): Collection
    {
        return User::query()
            ->where(function (Builder $query) {
                $query->whereIn('role', ['employee', 'manager'])
                    ->orWhereHas('roles', fn (Builder $roles) => $roles->whereIn('name', ['employee', 'manager']));
            })
            ->when($this->selectedUserId !== '', fn (Builder $query) => $query->whereKey($this->selectedUserId))
            ->when($this->selectedProjectId !== '', fn (Builder $query) => $query->where('project_id', $this->selectedProjectId))
            ->withCount([
                'dailyReportEntries as report_entries_count' => fn (Builder $query) => $this->applyFilters($query),
                'assignedTasks as completed_tasks_count' => fn (Builder $query) => $query
                    ->where('status', 'completed')
                    ->when($this->selectedDate !== '', fn (Builder $query) => $query->whereDate('updated_at', $this->selectedDate))
                    ->when($this->selectedProjectId !== '', fn (Builder $query) => $query->where('project_id', $this->selectedProjectId)),
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function getAllEmployees(): Collection
    {
        return User::query()
            ->where(function (Builder $query) {
                $query->whereIn('role', ['employee', 'manager'])
                    ->orWhereHas('roles', fn (Builder $roles) => $roles->whereIn('name', ['employee', 'manager']));
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getProjects(): Collection
    {
        return Project::query()->whereIn('name', ['BPOVO', 'GITMIT'])->orderBy('name')->get(['id', 'name']);
    }

    public function getUserEntries(int $userId): Collection
    {
        $entries = $this->applyFilters(
            DailyReportEntry::query()->with('project')->where('user_id', $userId)
        )->orderByDesc('report_date')->orderBy('slot_start')->get();

        if ($entries->isNotEmpty()) {
            return $entries;
        }

        return Task::query()
            ->with('project')
            ->where(function (Builder $query) use ($userId) {
                $query->where('assigned_to', $userId)
                    ->orWhere('user_id', $userId);
            })
            ->where('status', 'completed')
            ->when($this->selectedDate !== '', fn (Builder $query) => $query->whereDate('updated_at', $this->selectedDate))
            ->when($this->selectedProjectId !== '', fn (Builder $query) => $query->where('project_id', $this->selectedProjectId))
            ->latest('updated_at')
            ->get()
            ->map(function (Task $task) {
                $completedAt = $task->updated_at?->timezone('Asia/Karachi');

                return (object) [
                    'slot_label' => $completedAt ? 'Completed at '.$completedAt->format('g:i A') : 'Completed task',
                    'task_title' => $task->title,
                    'description' => $task->description,
                    'project' => $task->project,
                ];
            });
    }

    public function exportExcel(): BinaryFileResponse
    {
        $entries = $this->filteredEntries()->with(['user', 'project'])->orderByDesc('report_date')->orderBy('user_id')->orderBy('slot_start')->get();
        $filename = 'daily-reports-'.now()->format('Y-m-d-His').'.xlsx';
        $path = storage_path('app/'.$filename);
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(['Date', 'Employee', 'Time Slot', 'Task Title', 'Description', 'Project']));
        foreach ($entries as $entry) {
            $writer->addRow(Row::fromValues([$entry->report_date?->format('M d, Y'), $entry->user?->name ?? '—', $entry->slot_label, $entry->task_title ?: '—', $entry->description ?: '—', $entry->project?->name ?? '—']));
        }
        $writer->close();

        return response()->download($path, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend(true);
    }

    public function getPdfUrl(): string
    {
        return route('daily-reports.pdf', array_filter(['date' => $this->selectedDate, 'user' => $this->selectedUserId, 'project' => $this->selectedProjectId], fn ($v) => $v !== ''));
    }

    protected function filteredEntries(): Builder
    {
        return $this->applyFilters(DailyReportEntry::query());
    }

    protected function applyFilters(Builder $q): Builder
    {
        return $q->when($this->selectedDate !== '', fn (Builder $q) => $q->whereDate('report_date', $this->selectedDate))->when($this->selectedUserId !== '', fn (Builder $q) => $q->where('user_id', $this->selectedUserId))->when($this->selectedProjectId !== '', fn (Builder $q) => $q->where('project_id', $this->selectedProjectId));
    }
}
