<?php

namespace App\Filament\Pages;

use App\Models\DailyReportEntry;
use App\Models\Project;
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
        return User::query()->when($this->selectedUserId !== '', fn (Builder $q) => $q->whereKey($this->selectedUserId))
            ->whereHas('dailyReportEntries', fn (Builder $q) => $this->applyFilters($q))->orderBy('name')->get(['id', 'name', 'email']);
    }

    public function getAllEmployees(): Collection
    {
        return User::query()->orderBy('name')->get(['id', 'name']);
    }

    public function getProjects(): Collection
    {
        return Project::query()->whereIn('name', ['BPOVO', 'GITMIT'])->orderBy('name')->get(['id', 'name']);
    }

    public function getUserEntries(int $userId): Collection
    {
        return $this->applyFilters(DailyReportEntry::query()->with('project')->where('user_id', $userId))->orderByDesc('report_date')->orderBy('slot_start')->get();
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
