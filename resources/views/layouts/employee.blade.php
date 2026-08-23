<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Employee Dashboard - Performance Management</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Phosphor Icons for UI -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #FAFAFB; /* Soft light background */
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #E2E8F0;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #CBD5E1;
        }
        /* Smooth transitions */
        .transition-all-fast {
            transition: all 0.2s ease-in-out;
        }
        @media (min-width: 768px) {
            body { padding: 4px; }
            #sidebar { border-radius: 1.5rem 0 0 1.5rem; box-shadow: 8px 0 30px rgba(79, 70, 229, .04); }
            main { border-radius: 0 1.5rem 1.5rem 0; }
        }
        #sidebar nav a.bg-indigo-50 {
            background: linear-gradient(115deg, #4f2cff, #9f7aea) !important;
            box-shadow: 0 12px 20px rgba(91, 57, 238, .22);
            color: #fff !important;
        }
        #sidebar nav a.bg-indigo-50 i { color: #fff !important; }
        .employee-dashboard { background-image: radial-gradient(circle at 75% 2%, rgba(221, 214, 254, .24), transparent 21rem); }
        .employee-welcome h2 { color: #102451; font-size: clamp(1.65rem, 2.4vw, 2.15rem); letter-spacing: -.035em; }
        .employee-stat-card { min-height: 14.75rem; box-shadow: 0 12px 28px rgba(38, 44, 73, .08); }
        .employee-stat-card h3, .employee-stat-card p { position: relative; z-index: 1; }
        .employee-stat-card h3 { color: #0f2249; }
        .employee-stat-art { bottom: 1.4rem; font-size: 6.75rem; opacity: .13; position: absolute; right: .8rem; }
        .employee-stat-pending .employee-stat-art { color: #6d4aff; }
        .employee-stat-progress .employee-stat-art { color: #f59e0b; }
        .employee-stat-completed .employee-stat-art { color: #22c55e; }
        .employee-stat-overdue .employee-stat-art { color: #fb4a68; }
        .employee-create-card { box-shadow: 0 14px 35px rgba(38, 44, 73, .08); }
        .employee-create-art { bottom: -1.9rem; color: #8b72ff; font-size: 10rem; opacity: .16; position: absolute; right: 2rem; transform: rotate(10deg); }
        @media (max-width: 640px) {
            .employee-stat-card { min-height: 11.5rem; }
            .employee-stat-art { font-size: 5rem; }
            .employee-create-art { font-size: 7rem; right: .5rem; }
        }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen overflow-hidden flex flex-col md:flex-row">
    @php
        $notificationUser = auth()->user();
        $notificationTasks = $notificationUser?->assignedTasks()->with('creator')->latest()->take(5)->get() ?? collect();
        $unreadNotificationCount = $notificationUser
            ? $notificationUser->assignedTasks()
                ->when($notificationUser->task_notifications_read_at, fn ($query, $readAt) => $query->where('created_at', '>', $readAt))
                ->count()
            : 0;
    @endphp

    <!-- Mobile Top Header with Image Logo -->
    <div class="md:hidden flex items-center justify-between px-6 py-4 bg-white border-b border-slate-100 z-50">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center">
                <img src="{{ asset('images/bpovo_logo.png') }}" alt="BPOVO Logo" class="h-15 w-auto object-contain">
            </div>
            <h2 class="font-bold text-base text-slate-800 tracking-tight leading-tight">Performance<br>Management</h2>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <button type="button" data-notification-toggle class="relative p-2 text-slate-500 hover:text-indigo-600 transition-colors" aria-label="Recent tasks">
                    <i class="ph ph-bell text-2xl"></i>
                    <span data-notification-badge class="{{ $unreadNotificationCount ? '' : 'hidden' }} absolute -top-0.5 -right-0.5 min-w-5 h-5 px-1 flex items-center justify-center rounded-full bg-red-500 border-2 border-white text-[10px] font-bold text-white">{{ $unreadNotificationCount }}</span>
                </button>
                <div data-notification-panel class="hidden fixed top-16 right-4 left-4 sm:absolute sm:top-full sm:right-0 sm:left-auto sm:mt-2 sm:w-80 bg-white rounded-xl shadow-xl border border-slate-100 z-50 overflow-hidden">
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
            <button id="mobile-menu-btn" class="text-slate-500 hover:text-indigo-600 transition-colors">
                <i class="ph ph-list text-2xl"></i>
            </button>
        </div>
    </div>

    <!-- Sidebar (Desktop) / Mobile Menu Slide-over -->
    <aside id="sidebar" class="fixed md:static inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-100 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col h-full overflow-y-auto pt-20 md:pt-0">
        
       <!-- Logo (Desktop) with Image -->
       <div class="hidden md:flex items-center gap-3 px-6 py-8">
           <div class="flex items-center justify-center">
               <img src="{{ asset('images/bpovo_logo.png') }}" alt="BPOVO Logo" class="h-16 w-auto object-contain">
           </div>
           <h1 class="font-bold text-xl text-slate-800 tracking-tight leading-tight">Performance<br>Management</h1>
       </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-4 space-y-1 mb-8">
            <a href="{{ route('employee.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl font-semibold transition-all-fast {{ request()->routeIs('employee.dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:bg-slate-50 hover:text-indigo-600' }}">
                <i class="ph-fill ph-squares-four text-xl"></i>
                Dashboard
            </a>
            
            <div class="pt-4 pb-2 px-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tasks</p>
            </div>
            <a href="{{ route('employee.tasks.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-medium transition-all-fast {{ request()->routeIs('employee.tasks.index') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:bg-slate-50 hover:text-indigo-600' }}">
                <i class="ph ph-check-square text-xl"></i>
                All Tasks
            </a>
            <a href="{{ route('employee.history') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-medium transition-all-fast {{ request()->routeIs('employee.history') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:bg-slate-50 hover:text-indigo-600' }}">
                <i class="ph ph-clock-counter-clockwise text-xl"></i>
                Task History
            </a>
            <a href="{{ route('employee.activities') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-medium transition-all-fast {{ request()->routeIs('employee.activities') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:bg-slate-50 hover:text-indigo-600' }}">
                <i class="ph ph-list-checks text-xl"></i>
                Activities
            </a>

            <div class="pt-4 pb-2 px-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Account</p>
            </div>
            <a href="{{ route('employee.profile') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-medium transition-all-fast {{ request()->routeIs('employee.profile') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:bg-slate-50 hover:text-indigo-600' }}">
                <i class="ph ph-user text-xl"></i>
                Profile
            </a>
            <a href="{{ route('employee.settings') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl font-medium transition-all-fast {{ request()->routeIs('employee.settings') ? 'bg-indigo-50 text-indigo-700' : 'text-slate-500 hover:bg-slate-50 hover:text-indigo-600' }}">
                <i class="ph ph-gear text-xl"></i>
                Settings
            </a>
            <div class="p-4 border-t border-slate-100">
    <form method="POST" action="{{ route('employee.logout') }}">
        @csrf
        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium transition-all-fast shadow-sm shadow-indigo-100">
            <i class="ph ph-sign-out text-xl"></i>
            Logout
        </button>
    </form>
</div>
        </nav>
        
        <!-- Logout Button at bottom -->
        
    </aside>

    <!-- Overlay for mobile sidebar -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm z-30 hidden md:hidden transition-opacity"></div>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col md:flex-row h-full overflow-hidden relative">
        @yield('content')
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            let isSidebarOpen = false;

            function toggleSidebar() {
                isSidebarOpen = !isSidebarOpen;
                if (isSidebarOpen) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                } else {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            }

            mobileMenuBtn?.addEventListener('click', toggleSidebar);
            overlay?.addEventListener('click', toggleSidebar);

            const notificationPanels = document.querySelectorAll('[data-notification-panel]');
            const emptyNotificationMarkup = '<p class="px-4 py-5 text-sm text-slate-500">No recent tasks.</p>';

            function updateNotificationBadge(count) {
                document.querySelectorAll('[data-notification-badge]').forEach((badge) => {
                    badge.textContent = count;
                    badge.classList.toggle('hidden', count < 1);
                });
            }

            function updateNotificationLists(tasks) {
                document.querySelectorAll('[data-notification-list]').forEach((list) => {
                    list.replaceChildren();
                    if (!tasks.length) {
                        list.innerHTML = emptyNotificationMarkup;
                        return;
                    }
                    tasks.forEach((task) => {
                        const link = document.createElement('a');
                        link.href = '{{ route('employee.tasks.index') }}';
                        link.className = 'block px-4 py-3 border-b border-slate-50 hover:bg-slate-50';
                        const title = document.createElement('p');
                        title.className = 'text-sm font-semibold text-slate-800 truncate';
                        title.textContent = task.title;
                        const detail = document.createElement('p');
                        detail.className = 'text-xs text-slate-500 mt-1';
                        detail.textContent = `${task.assignedBy} · ${task.createdAt}`;
                        link.append(title, detail);
                        list.appendChild(link);
                    });
                });
            }

            async function refreshNotifications() {
                try {
                    const response = await fetch('{{ route('employee.notifications') }}', { headers: { Accept: 'application/json' } });
                    if (!response.ok) return;
                    const data = await response.json();
                    updateNotificationBadge(data.unreadCount);
                    updateNotificationLists(data.tasks);
                } catch (_) {
                    // The next poll will retry when the connection is available.
                }
            }

            async function markNotificationsRead() {
                updateNotificationBadge(0);
                try {
                    await fetch('{{ route('employee.notifications.read') }}', {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: '{}',
                    });
                } catch (_) {
                    refreshNotifications();
                }
            }

            document.querySelectorAll('[data-notification-toggle]').forEach((toggle) => {
                toggle.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const panel = toggle.nextElementSibling;
                    notificationPanels.forEach((item) => {
                        if (item !== panel) item.classList.add('hidden');
                    });
                    panel?.classList.toggle('hidden');
                    markNotificationsRead();
                });
            });
            document.addEventListener('click', () => notificationPanels.forEach((panel) => panel.classList.add('hidden')));
            refreshNotifications();
            window.setInterval(refreshNotifications, 15000);
        });
    </script>
</body>
</html>
