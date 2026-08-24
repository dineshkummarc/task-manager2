@extends('layouts.employee')

@section('content')
@php
    $notificationTasks = $user->assignedTasks()->with('creator')->latest()->take(5)->get();
    $unreadNotificationCount = $user->assignedTasks()
        ->when($user->task_notifications_read_at, fn ($query, $readAt) => $query->where('created_at', '>', $readAt))
        ->count();
@endphp
<!-- Scrollable Main Content -->
<div class="flex-1 overflow-y-auto bg-[#FAFAFB] p-6 lg:p-8">
    
    <!-- Top Bar (Desktop) -->
    <div class="hidden md:flex justify-end items-center mb-8">
        <div class="flex items-center gap-4">
            <div class="relative">
                <button type="button" data-notification-toggle class="relative p-2 text-slate-400 hover:text-indigo-600 transition-colors" aria-label="Recent tasks">
                <i class="ph ph-bell text-2xl"></i>
                <span data-notification-badge class="{{ $unreadNotificationCount ? '' : 'hidden' }} absolute -top-0.5 -right-0.5 min-w-5 h-5 px-1 flex items-center justify-center rounded-full bg-red-500 border-2 border-[#FAFAFB] text-[10px] font-bold text-white">{{ $unreadNotificationCount }}</span>
                </button>
                <div data-notification-panel class="hidden absolute right-0 top-full mt-2 w-80 bg-white rounded-xl shadow-xl border border-slate-100 z-50 overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-100 font-bold text-slate-800">Recent Tasks</div>
                    <div data-notification-list class="max-h-72 overflow-y-auto">
                        @forelse ($notificationTasks as $task)
                            <a href="{{ route('employee.tasks.index') }}" class="block px-4 py-3 border-b border-slate-50 hover:bg-slate-50">
                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $task->title }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ $task->creator?->name ?? 'Self' }} · {{ $task->created_at->diffForHumans() }}</p>
                            </a>
                        @empty
                            <p class="px-4 py-5 text-sm text-slate-500">No recent tasks.</p>
                        @endforelse
                    </div>
                    <a href="{{ route('employee.tasks.index') }}" class="block px-4 py-3 text-center text-sm font-semibold text-indigo-600 hover:bg-indigo-50">View all tasks</a>
                </div>
            </div>
            <div class="relative group">
                <div class="flex items-center gap-3 pl-4 border-l border-slate-200 cursor-pointer">
                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff' }}" alt="Profile" class="w-10 h-10 rounded-full shadow-sm object-cover">
                    <div class="hidden lg:block">
                        <p class="text-sm font-bold text-slate-800 leading-tight">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500">{{ ucfirst($user->role) ?? 'Employee' }}</p>
                    </div>
                    <i class="ph ph-caret-down text-slate-400 text-xs ml-1"></i>
                </div>
                
                <!-- Dropdown -->
                <div class="absolute right-0 top-full mt-2 w-64 bg-white rounded-xl shadow-lg border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 p-4">
                    <div class="flex flex-col items-center pb-4 border-b border-slate-100">
                        <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff' }}" class="w-16 h-16 rounded-full mb-2 border-2 border-indigo-100 shadow-sm object-cover">
                        <h3 class="font-bold text-slate-800">{{ $user->name }}</h3>
                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                    </div>
                    <div class="pt-4 space-y-3">
                        <div class="flex gap-3 items-start">
                            <i class="ph ph-buildings text-slate-400 mt-0.5"></i>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Department</p>
                                <p class="text-sm font-medium text-slate-700">{{ $user->department->name ?? 'IT Department' }}</p>
                            </div>
                        </div>
                        <div class="flex gap-3 items-start">
                            <i class="ph ph-phone text-slate-400 mt-0.5"></i>
                            <div>
                                <p class="text-xs font-semibold text-slate-400 uppercase">Phone</p>
                                <p class="text-sm font-medium text-slate-700">{{ $user->phone ?? 'abc@gmail.com' }}</p>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-slate-100">
                             <form method="POST" action="{{ route('employee.logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-2 py-2 rounded-lg text-rose-500 hover:bg-rose-50 font-medium transition-all-fast text-sm">
                                    <i class="ph ph-sign-out text-lg"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Welcome Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2 -mt-20">
                Welcome back, {{ explode(' ', trim($user->name))[0] }}! 👋
            </h2>
            <p class="text-sm text-slate-500 mt-1">Here's what's happening with your tasks today.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('employee.daily-report.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                <i class="ph ph-note-pencil text-lg"></i>
                Daily Report
            </a>
            <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm text-sm font-medium text-slate-700">
                {{ now()->format('M d, Y') }}
                <i class="ph ph-calendar-blank text-lg text-slate-400"></i>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 mb-8 -mt-6">
        <!-- Pending Tasks -->
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="absolute bottom-0 left-0 w-full h-1 bg-indigo-500 rounded-b-2xl"></div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 transition-transform group-hover:scale-110">
                <i class="ph-fill ph-clipboard-text text-2xl"></i>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $pendingTasks ?? 0 }}</h3>
            <p class="text-sm font-semibold text-slate-800 mt-1">Pending Tasks</p>
            <p class="text-xs text-slate-500 mt-1">Tasks waiting to start</p>
        </div>
        
        <!-- In Progress -->
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="absolute bottom-0 left-0 w-full h-1 bg-amber-500 rounded-b-2xl"></div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center mb-4 transition-transform group-hover:scale-110">
                <i class="ph-fill ph-clock-countdown text-2xl"></i>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $inProgressTasks }}</h3>
            <p class="text-sm font-semibold text-slate-800 mt-1">In Progress</p>
            <p class="text-xs text-slate-500 mt-1">Tasks in progress</p>
        </div>

        <!-- Completed -->
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="absolute bottom-0 left-0 w-full h-1 bg-emerald-500 rounded-b-2xl"></div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center mb-4 transition-transform group-hover:scale-110">
                <i class="ph-fill ph-check-circle text-2xl"></i>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $completedTasks }}</h3>
            <p class="text-sm font-semibold text-slate-800 mt-1">Completed</p>
            <p class="text-xs text-slate-500 mt-1">Tasks completed</p>
        </div>

        <!-- Overdue -->
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="absolute bottom-0 left-0 w-full h-1 bg-rose-500 rounded-b-2xl"></div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center mb-4 transition-transform group-hover:scale-110">
                <i class="ph-fill ph-hourglass-high text-2xl"></i>
            </div>
            <h3 class="text-3xl font-bold text-slate-800">{{ $overdueTasks }}</h3>
            <p class="text-sm font-semibold text-slate-800 mt-1">Overdue</p>
            <p class="text-xs text-slate-500 mt-1">Tasks overdue</p>
        </div>
    </div>

    <!-- Create New Task Form -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm mb-8 overflow-hidden">
        <div class="p-5 md:p-6 border-b border-slate-100 bg-indigo-50/30">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill ph-plus-circle text-indigo-500"></i>
                Create New Task
            </h3>
        </div>
        <div class="p-5 md:p-6">
            <form action="{{ route('employee.tasks.store') }}" method="POST" data-async-form data-reset-on-success>
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Task Title</label>
                        <input type="text" name="title" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder:text-slate-400" placeholder="E.g., Update homepage banner">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Project</label>
                        <select name="project_id" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all">
                            <option value="">Select project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" @selected(old('project_id', $user->project_id) == $project->id)>{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Estimated Time (Minutes)</label>
                        <input type="number" name="estimated_minutes" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder:text-slate-400" placeholder="E.g., 60">
                    </div>
                </div>
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Description (Optional)</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder:text-slate-400" placeholder="Briefly describe what needs to be done..."></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" data-submit-button data-loading-text="Saving..." class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-colors shadow-sm flex items-center gap-2">
                        <span data-submit-label>Save Task</span>
                        <i class="ph ph-paper-plane-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- My Tasks List -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm mb-8">
        <div class="p-3 md:p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800">My Tasks</h3>
            <div class="inline-flex">
                <select id="task-status-filter" class="px-3 py-1.5 border border-slate-200 rounded-lg text-sm font-medium text-slate-600 bg-white hover:bg-slate-50 transition-colors focus:outline-none">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="blockage">Unresolved</option>
                    <option value="completed">Completed</option>
                </select>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <th class="py-4 px-6 font-semibold">#</th>
                        <th class="py-4 px-6 font-semibold">Task Title</th>
                        <th class="py-4 px-6 font-semibold">Assigned By</th>
                        <th class="py-4 px-6 font-semibold">Assigned At</th>
                        <th class="py-4 px-6 font-semibold text-center">Status</th>
                        <th class="py-4 px-6 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @forelse($tasks as $index => $task)
                    <tr data-task-row data-task-status="{{ $task->is_blocked ? 'blockage' : $task->status }}" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors group">
                        <td class="py-4 px-6 text-slate-400 font-medium">{{ $tasks->firstItem() + $index }}</td>
                        <td class="py-4 px-6">
                            <p class="font-bold text-slate-800 mb-0.5">{{ $task->title }}</p>
                            <p class="text-xs text-slate-500 truncate max-w-xs">{!! strip_tags($task->description) !!}</p>
                        </td>
                        <td class="py-4 px-6 text-sm font-medium text-slate-700 whitespace-nowrap">
                            {{ $task->assigned_by === $user->id ? 'Self' : ($task->creator?->name ?? '—') }}
                        </td>
                        <td class="py-4 px-6 text-xs text-slate-500 whitespace-nowrap">
                            {{ $task->created_at?->timezone('Asia/Karachi')->format('M d, Y h:i A') ?? '—' }}
                        </td>
                        <td class="py-4 px-6 text-center">
                            @php
                                $statusClass = match($task->status) {
                                    'completed' => 'bg-emerald-50 text-emerald-600 border border-emerald-200',
                                    'in_progress' => 'bg-indigo-50 text-indigo-600 border border-indigo-200',
                                    default => 'bg-amber-50 text-amber-600 border border-amber-200'
                                };
                                $statusLabel = match($task->status) {
                                    'completed' => 'Completed',
                                    'in_progress' => 'In Progress',
                                    default => 'Pending'
                                };
                                if ($task->is_blocked) {
                                    $statusClass = 'bg-red-50 text-red-600 border border-red-200';
                                    $statusLabel = 'Unresolved';
                                }
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="py-4 -px-5 text-right">
                            <form action="{{ route('employee.tasks.status', $task) }}" method="POST" class="inline-block w-full sm:w-44">
                                @csrf
                                <input type="hidden" name="block_reason" value="">
                                <input type="hidden" name="completion_note" value="">
                                <select name="status" data-task-status-select data-original-status="{{ $task->is_blocked ? 'blockage' : $task->status }}" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                                    <option value="pending" @selected($task->status === 'pending' && ! $task->is_blocked)>Pending</option>
                                    <option value="in_progress" @selected($task->status === 'in_progress' && ! $task->is_blocked)>In Progress</option>
                                    <option value="blockage" @selected($task->is_blocked)>Unresolved</option>
                                    <option value="completed" @selected($task->status === 'completed')>Completed</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-500">
                            No tasks found for you right now.
                        </td>
                    </tr>
                    @endforelse
                    <tr id="task-filter-empty" class="hidden">
                        <td colspan="6" class="py-8 text-center text-slate-500">
                            No tasks match this status.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        @if($tasks->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $tasks->links() }}
        </div>
        @else
        <div class="p-4 border-t border-slate-100 flex items-center justify-between text-sm text-slate-500">
            <span id="task-count">Showing {{ $tasks->count() }} of {{ $tasks->total() }} tasks</span>
            <div class="flex gap-1">
                <button class="w-8 h-8 rounded border border-slate-200 flex items-center justify-center opacity-50 cursor-not-allowed"><i class="ph ph-caret-left"></i></button>
                <button class="w-8 h-8 rounded bg-indigo-600 text-white flex items-center justify-center font-medium">1</button>
                <button class="w-8 h-8 rounded border border-slate-200 flex items-center justify-center opacity-50 cursor-not-allowed"><i class="ph ph-caret-right"></i></button>
            </div>
        </div>
        @endif
    </div>

    <div id="task-status-modal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl">
            <div class="mb-5 flex items-start justify-between gap-4"><div><h3 id="task-status-modal-title" class="text-lg font-bold text-slate-800">Update task</h3><p id="task-status-modal-help" class="mt-1 text-sm text-slate-500"></p></div><button type="button" data-status-modal-cancel class="text-2xl leading-none text-slate-400 hover:text-slate-600">×</button></div>
            <textarea id="task-status-modal-input" rows="4" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20" placeholder="Write a note..."></textarea>
            <p id="task-status-modal-error" class="mt-2 hidden text-xs font-medium text-rose-600"></p>
            <div class="mt-5 flex justify-end gap-3"><button type="button" data-status-modal-cancel class="rounded-xl px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancel</button><button type="button" id="task-status-modal-save" class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Save status</button></div>
        </div>
    </div>

    <!-- Bottom Sections Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-6">
        
        <!-- Recent Activity -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="p-5 md:p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-800">Recent Activity</h3>
                <a href="{{ route('employee.activities') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">View All</a>
            </div>
            <div class="p-5 md:p-6 space-y-6">
                @forelse($recentActivity as $activity)
                <div class="flex gap-4">
                    <div class="relative mt-1">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center z-10 relative">
                            <i class="ph-fill ph-check-circle text-lg"></i>
                        </div>
                        @if(!$loop->last)
                        <div class="absolute top-8 left-1/2 -translate-x-1/2 w-px h-10 bg-slate-200"></div>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $activity->description ?? ($activity->action . ' task') }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-slate-500 text-sm">
                    No recent activity to show.
                </div>
                @endforelse
                
                <!-- Mock Activity for UI visualization if DB is empty -->
                @if($recentActivity->isEmpty())
                <div class="flex gap-4">
                    <div class="relative mt-1">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center z-10 relative">
                            <i class="ph-fill ph-play-circle text-lg"></i>
                        </div>
                        <div class="absolute top-8 left-1/2 -translate-x-1/2 w-px h-10 bg-slate-200"></div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">You started working on '<span class="font-bold">Fix Dashboard Bug</span>'</p>
                        <p class="text-xs text-slate-500 mt-1">3 hours ago</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="relative mt-1">
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center z-10 relative">
                            <i class="ph-fill ph-file-text text-lg"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">New task '<span class="font-bold">API Integration</span>' assigned to you</p>
                        <p class="text-xs text-slate-500 mt-1">1 day ago</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Upcoming Deadlines -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="p-5 md:p-6 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-lg font-bold text-slate-800">Upcoming Deadlines</h3>
                <a href="#" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">View Calendar</a>
            </div>
            <div class="p-5 md:p-6 space-y-4">
                @forelse($upcomingDeadlines as $deadline)
                <div class="flex items-center gap-4 p-3 rounded-xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center shrink-0">
                        <i class="ph ph-calendar-blank text-xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-800 truncate">{{ $deadline->title }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($deadline->due_date)->format('M d, Y') }}</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded text-[10px] font-bold bg-rose-50 text-rose-600 uppercase tracking-wide">
                        Urgent
                    </span>
                </div>
                @empty
                <div class="text-center py-4 text-slate-500 text-sm">
                    No upcoming deadlines.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const filter = document.getElementById('task-status-filter');
        const rows = [...document.querySelectorAll('[data-task-row]')];
        const emptyState = document.getElementById('task-filter-empty');
        const count = document.getElementById('task-count');

        filter?.addEventListener('change', () => {
            const selectedStatus = filter.value;
            let visibleRows = 0;

            rows.forEach((row) => {
                const shouldShow = !selectedStatus || row.dataset.taskStatus === selectedStatus;
                row.classList.toggle('hidden', !shouldShow);
                visibleRows += Number(shouldShow);
            });

            emptyState?.classList.toggle('hidden', visibleRows > 0);

            if (count) {
                count.textContent = `Showing ${visibleRows} of ${rows.length} tasks`;
            }
        });

        const modal = document.getElementById('task-status-modal');
        const modalInput = document.getElementById('task-status-modal-input');
        const modalTitle = document.getElementById('task-status-modal-title');
        const modalHelp = document.getElementById('task-status-modal-help');
        const modalError = document.getElementById('task-status-modal-error');
        let selectedStatusControl = null;

        const closeStatusModal = () => { modal.classList.add('hidden'); modal.classList.remove('flex'); selectedStatusControl = null; };
        document.querySelectorAll('[data-status-modal-cancel]').forEach((button) => button.addEventListener('click', () => {
            if (selectedStatusControl) selectedStatusControl.value = selectedStatusControl.dataset.originalStatus;
            closeStatusModal();
        }));
        document.querySelectorAll('[data-task-status-select]').forEach((select) => select.addEventListener('change', () => {
            if (!['completed', 'blockage'].includes(select.value)) { select.form.submit(); return; }
            selectedStatusControl = select;
            const isBlocked = select.value === 'blockage';
            modalTitle.textContent = isBlocked ? 'Mark task as unresolved' : 'Mark task as completed';
            modalHelp.textContent = isBlocked ? 'Please explain what is blocking this task.' : 'Add a completion note (optional).';
            modalInput.placeholder = isBlocked ? 'Describe the issue or blocker...' : 'What was completed?';
            modalInput.value = isBlocked ? select.form.querySelector('[name=block_reason]').value : select.form.querySelector('[name=completion_note]').value;
            modalError.classList.add('hidden'); modal.classList.remove('hidden'); modal.classList.add('flex'); modalInput.focus();
        }));
        document.getElementById('task-status-modal-save')?.addEventListener('click', () => {
            if (!selectedStatusControl) return;
            const isBlocked = selectedStatusControl.value === 'blockage';
            if (isBlocked && !modalInput.value.trim()) { modalError.textContent = 'Please enter the reason for this unresolved task.'; modalError.classList.remove('hidden'); return; }
            selectedStatusControl.form.querySelector(isBlocked ? '[name=block_reason]' : '[name=completion_note]').value = modalInput.value.trim();
            selectedStatusControl.form.submit();
        });
    });
</script>

@endsection
