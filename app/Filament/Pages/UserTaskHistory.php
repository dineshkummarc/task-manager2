<?php

namespace App\Filament\Pages;

use App\Models\Task;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserTaskHistory extends Page
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Employee Reports';

    protected static ?int $navigationSort = 4;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $slug = 'task-history';

    protected static string $view = 'filament.pages.user-task-history';

    public string $period = 'today';

    public mixed $selectedUserId = null;

    public string $search = '';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Admin ya 'view_reports' permission walay user ko hi access milegi
        return $user->isAdmin() || $user->can('view_reports');
    }

    public function mount(): void
    {
        $user = auth()->user();

        abort_unless($user && ($user->isAdmin() || $user->isManager() || $user->isEmployee()), 403);

        $requestedPeriod = request()->string('period')->toString();
        if (in_array($requestedPeriod, array_keys($this->getPeriodOptions()), true)) {
            $this->period = $requestedPeriod;
        }

        if (! $this->canFilterUsers()) {
            $this->selectedUserId = $user->id;

            return;
        }

        $requestedUserId = request()->integer('user');

        if ($requestedUserId && User::query()->whereKey($requestedUserId)->exists()) {
            $this->selectedUserId = $requestedUserId;
        }
    }

    public function getTitle(): string
    {
        return 'Employee Reports';
    }

    public function getHeading(): string
    {
        return 'Employee Reports';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPeriod(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedUserId($value): void
    {
        $this->selectedUserId = ($value === '' || $value === null) ? null : (int) $value;
        $this->resetPage();
    }

    public function selectUser($userId = null): void
    {
        if (! $this->canFilterUsers()) {
            $this->selectedUserId = auth()->id();
            $this->resetPage();

            return;
        }

        $this->selectedUserId = $userId ? (int) $userId : null;
        $this->resetPage();
    }

    public function canFilterUsers(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isAdmin() || $user?->isManager());
    }

    public function getUsers(): Collection
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getPeriodOptions(): array
    {
        return [
            'today' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'overall' => 'Overall',
        ];
    }

    public function getPeriodLabel(): string
    {
        return $this->getPeriodOptions()[$this->period] ?? 'Daily';
    }

    public function getSelectedUserLabel(): string
    {
        if (! $this->selectedUserId) {
            return 'All Users';
        }

        return User::query()->whereKey($this->selectedUserId)->value('name') ?: 'All Users';
    }

    public function getGroupedUsers(): LengthAwarePaginator
    {
        $search = trim($this->search);

        $users = User::query()
            ->when(! $this->canFilterUsers(), fn (Builder $query) => $query->whereKey(auth()->id()))
            ->when($this->selectedUserId, fn (Builder $query) => $query->whereKey($this->selectedUserId))
            ->when($search !== '', fn (Builder $query) => $query->where('name', 'like', '%' . $search . '%'))
            ->whereHas('assignedTasks', function (Builder $query) {
                $query->where('status', 'completed');
                $this->applyPeriodFilter($query);
            })
            ->orderBy('name')
            ->paginate(10);

        $users->getCollection()->transform(function (User $user) {
            $tasks = $this->applyPeriodFilter(
                $user->assignedTasks()->where('status', 'completed')
            )
                ->latest('updated_at')
                ->get();

            $user->setRelation('historyTasks', $tasks);

            return $user;
        });

        return $users;
    }

    public function getAvatarUrl(User $user): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff&bold=true';
    }

    protected function completedTasksQuery(): Builder
    {
        $search = trim($this->search);

        $query = Task::query()
            ->with('assignee')
            ->where('status', 'completed');

        if ($this->selectedUserId) {
            $query->where('assigned_to', $this->selectedUserId);
        } elseif (! $this->canFilterUsers()) {
            $query->where('assigned_to', auth()->id());
        }

        if ($search !== '') {
            $query->whereHas('assignee', fn (Builder $builder) => $builder->where('name', 'like', '%' . $search . '%'));
        }

        return $this->applyPeriodFilter($query);
    }

    protected function applyPeriodFilter(Builder|Relation $query): Builder|Relation
    {
        $tz = 'Asia/Karachi';

        return match ($this->period) {
            'weekly' => $query->whereBetween('updated_at', [
                now($tz)->startOfWeek()->timezone('UTC'),
                now($tz)->endOfWeek()->timezone('UTC'),
            ]),
            'monthly' => $query->whereBetween('updated_at', [
                now($tz)->startOfMonth()->timezone('UTC'),
                now($tz)->endOfMonth()->timezone('UTC'),
            ]),
            'overall' => $query,
            default => $query->whereBetween('updated_at', [
                now($tz)->startOfDay()->timezone('UTC'),
                now($tz)->endOfDay()->timezone('UTC'),
            ]),
        };
    }

    public function exportExcel(): BinaryFileResponse
    {
        $tasks = $this->completedTasksQuery()->latest('updated_at')->get();
        $userSlug = 'all-users';

        if ($this->selectedUserId) {
            $userSlug = Str::slug(User::query()->whereKey($this->selectedUserId)->value('name') ?: 'user');
        }

        $filename = 'completed-tasks-' . $userSlug . '.xlsx';
        $path = storage_path('app/' . $filename);

        $writer = new Writer();
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(['User Name', 'Task Name', 'Details', 'Assigned At', 'Completed At']));

        foreach ($tasks as $task) {
            $writer->addRow(Row::fromValues([
                $task->assignee?->name ?? '—',
                $task->title,
                trim(strip_tags((string) $task->description)),
                $task->created_at
                    ?->timezone('Asia/Karachi')
                    ->format('M d, Y h:i A'),
                $task->updated_at
                    ?->timezone('Asia/Karachi')
                    ->format('M d, Y h:i A'),
            ]));
        }

        $writer->close();

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
