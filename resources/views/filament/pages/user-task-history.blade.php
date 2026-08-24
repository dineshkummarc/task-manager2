<x-filament-panels::page>
    @php
        $groupedUsers = $this->getGroupedUsers();
    @endphp

    <style>
        .th-page { font-family: 'Inter', system-ui, sans-serif; }

        /* Filter bar */
        .th-filters {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .th-input-wrap {
            position: relative;
            flex: 1;
            min-width: 180px;
            max-width: 260px;
        }
        .th-input-wrap .th-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            pointer-events: none;
        }
        .th-input {
            width: 100%;
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 7px 12px 7px 32px;
            font-size: 13px;
            color: #374151;
            background: #fff;
            outline: none;
            transition: border 0.15s;
        }
        .th-input:focus { border-color: #6366F1; }
        .th-select {
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 7px 30px 7px 32px;
            font-size: 13px;
            color: #374151;
            background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%239CA3AF' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E") no-repeat right 10px center;
            background-size: 16px;
            outline: none;
            cursor: pointer;
            transition: border 0.15s;
        }
        .th-select:focus { border-color: #6366F1; }
        .th-select::-ms-expand { display: none; }

        .th-btn-excel {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            background: #059669;
            color: #fff;
            border: none;
            cursor: pointer;
            transition: background 0.15s;
        }
        .th-btn-note { background: #ecfdf3; border: 1px solid #86efac; border-radius: 7px; color: #15803d; cursor: pointer; font-size: 11px; font-weight: 700; padding: 5px 9px; white-space: nowrap; }
        .th-btn-note:hover { background: #dcfce7; }
        .th-no-note { color: #94a3b8; }
        .th-note-overlay { align-items: center; background: rgba(15, 23, 42, .45); display: flex; inset: 0; justify-content: center; padding: 1rem; position: fixed; z-index: 100; }
        .th-note-modal { background: #fff; border-radius: 12px; box-shadow: 0 20px 45px rgba(15, 23, 42, .25); max-width: 440px; padding: 24px; position: relative; width: 100%; }
        .th-note-modal h3 { color: #0f172a; font-size: 18px; font-weight: 800; margin: 0; }
        .th-note-task { color: #64748b; font-size: 13px; font-weight: 600; margin: 4px 0 16px; }
        .th-note-text { background: #f8fafc; border-radius: 8px; color: #334155; line-height: 1.6; margin: 0; padding: 12px; white-space: pre-wrap; }
        .th-note-close { background: transparent; border: 0; color: #94a3b8; cursor: pointer; font-size: 24px; position: absolute; right: 13px; top: 9px; }
        .th-btn-excel:hover { background: #047857; }
        .th-btn-pdf {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 7px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            background: #E11D48;
            color: #fff;
            text-decoration: none;
            transition: background 0.15s;
        }
        .th-btn-pdf:hover { background: #BE123C; }

        /* Main card */
        .th-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.07), 0 4px 16px rgba(0,0,0,0.04);
            overflow: hidden;
        }

        /* Grid table */
        .th-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .th-grid thead th {
            font-size: 11px;
            font-weight: 600;
            color: #9CA3AF;
            text-transform: uppercase;
            letter-spacing: .05em;
            text-align: left;
            padding: 12px 20px;
            border-bottom: 1.5px solid #F3F4F6;
            background: #FAFAFA;
        }
        .th-grid thead th:first-child { width: 240px; }

        .th-row {
            border-bottom: 1px solid #F3F4F6;
            vertical-align: top;
        }
        .th-row:last-child { border-bottom: none; }
        .th-row:hover { background: #FAFAFA; }

        /* Employee cell (left) */
        .th-emp-cell {
            padding: 16px 20px;
            width: 240px;
            border-right: 1px solid #F3F4F6;
        }
        .th-emp-inner {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .th-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #E0E7FF;
            flex-shrink: 0;
        }
        .th-user-info { min-width: 0; }
        .th-user-name {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .th-user-meta {
            font-size: 11px;
            color: #6B7280;
            margin-top: 1px;
        }

        /* Tasks cell */
        .th-tasks-cell {
            padding: 14px 20px;
        }
        .th-task-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .th-chip {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 180px;
            max-width: 240px;
            background: #FAFAFA;
            border: 1px solid #F0F1F3;
            border-radius: 10px;
            padding: 9px 12px;
        }
        .th-chip-top {
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        .th-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            margin-top: 5px;
            flex-shrink: 0;
        }
        .th-dot-indigo { background: #6366F1; }
        .th-dot-emerald { background: #10B981; }

        .th-task-title {
            font-size: 12.5px;
            font-weight: 600;
            color: #1F2937;
            line-height: 1.35;
        }
        .th-task-desc {
            font-size: 10.5px;
            color: #9CA3AF;
            margin-top: 1px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .th-chip-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
        }
        .th-badge-done {
            background: #D1FAE5;
            color: #065F46;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 999px;
            white-space: nowrap;
        }
        .th-date {
            display: flex;
            align-items: center;
            gap: 3px;
            font-size: 10px;
            color: #9CA3AF;
            white-space: nowrap;
        }

        /* Pagination footer */
        .th-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
            padding: 12px 20px;
            border-top: 1px solid #F3F4F6;
            font-size: 12px;
            color: #6B7280;
        }

        /* Empty state */
        .th-empty {
            text-align: center;
            padding: 48px 20px;
            color: #9CA3AF;
            font-size: 13px;
        }

        .th-report-title { margin: 0; padding: 14px 18px 10px; font-size: 14px; font-weight: 700; line-height: 1.35; color: #111827; }
        .th-report-table { width: 100%; border-collapse: collapse; }
        
        /* Table text sizes increased slightly here */
        .th-report-table th { padding: 12px 16px; background: #F8FAFC; border-bottom: 1px solid #E5E7EB; color: #64748B; font-size: 11px; font-weight: 700; text-align: left; text-transform: uppercase; letter-spacing: 0.03em; }
        .th-report-table td { padding: 12px 16px; border-bottom: 1px solid #EEF2F7; color: #334155; font-size: 13px; vertical-align: middle; }
        
        /* Prominent thick bottom border for all cells on the last row of a user group */
        .th-report-table tr.th-user-last-row td {
            border-bottom: 3px solid #94A3B8 !important;
        }

        .th-report-table tr:last-child td { border-bottom: none; }
        .th-report-table tr:nth-child(odd) td { background: #FCFDFE; }
        
        /* Employee cell */
        .th-report-employee { width: 145px; border-bottom: 3px solid #94A3B8 !important; border-right: 1px solid #E5E7EB; background: #F5F8FF !important; color: #1E293B !important; font-size: 14px; font-weight: 700; text-align: center; vertical-align: middle !important; white-space: nowrap; }
        
        .th-report-work { min-width: 150px; color: #0F172A !important; font-weight: 600; font-size: 13.5px; }
        .th-report-estimate { width: 130px; white-space: nowrap; font-size: 12.5px; color: #475569; }
        .th-report-description { color: #475569 !important; font-size: 12.5px; }
        .th-report-completed { width: 130px; color: #15803D !important; font-weight: 700; white-space: nowrap; font-size: 13px; }

        /* Dark mode */
        .dark .th-input,
        .dark .th-select { background-color: #111827; border-color: #374151; color: #E5E7EB; }
        .dark .th-card { background: #111827; box-shadow: 0 1px 3px rgba(0,0,0,0.3); }
        .dark .th-grid thead th { background: #1F2937; border-color: #1F2937; color: #9CA3AF; }
        .dark .th-emp-cell { border-color: #1F2937; }
        .dark .th-row { border-color: #1F2937; }
        .dark .th-row:hover { background: #1F2937; }
        .dark .th-user-name { color: #F9FAFB; }
        .dark .th-chip { background: #1F2937; border-color: #2D3748; }
        .dark .th-task-title { color: #F3F4F6; }
        .dark .th-footer { border-color: #1F2937; }
        .dark .th-report-table tr.th-user-last-row td { border-bottom: 3px solid #64748B !important; }
        .dark .th-report-employee { border-bottom: 3px solid #64748B !important; }

        /* Mobile responsive */
        @media (max-width: 640px) {
            .th-page {
                width: 100%;
                max-width: 100%;
                overflow-x: hidden;
            }

            .th-filters {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
                margin-bottom: 14px;
                width: 100%;
            }
            .th-input-wrap {
                width: 100%;
                min-width: 0;
                max-width: none !important;
            }
            .th-filters .th-input-wrap:first-child { grid-column: 1 / -1; }
            .th-input, .th-select {
                box-sizing: border-box;
                min-height: 38px;
                font-size: 12px;
                border-radius: 9px;
            }
            .th-btn-excel, .th-btn-pdf {
                justify-content: center;
                min-height: 36px;
                padding: 7px 12px;
                font-size: 11px;
                border-radius: 9px;
            }
            .th-filters > div[style*="flex:1"] { display: none; }
            .th-filters .th-btn-excel,
            .th-filters .th-btn-pdf { width: 100%; box-sizing: border-box; }

            .th-card { width: 100%; border-radius: 12px; overflow-x: auto; }
            .th-report-table th, .th-report-table td { padding: 10px; font-size: 11px; }
            
            .th-report-estimate { display: none; }
            .th-report-description { display: table-cell; font-size: 11px; color: #475569; max-width: 130px; word-break: break-word; }

            .th-report-employee { width: 80px; overflow: hidden; text-overflow: ellipsis; font-size: 11px; }
            .th-report-completed { width: 75px; font-size: 11px; }

            .th-grid, .th-grid thead, .th-grid tbody, .th-grid tr, .th-grid td, .th-grid th {
                display: block;
                width: 100%;
            }
            .th-grid thead { display: none; }
            .th-row { border-bottom: 8px solid #F3F4F6; }
            .th-emp-cell {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #F3F4F6;
                padding: 12px;
            }
            .th-avatar { width: 34px; height: 34px; }
            .th-user-name { font-size: 13px; }
            .th-user-meta { font-size: 10px; }
            .th-tasks-cell { padding: 10px 12px; }
            .th-task-chips { flex-direction: column; gap: 8px; }
            .th-chip { min-width: 0; max-width: 100%; }

            .th-footer { padding: 10px 12px; font-size: 11px; gap: 7px; }
            .th-footer > div { max-width: 100%; overflow-x: auto; }
        }

        @media (max-width: 380px) {
            .th-filters { grid-template-columns: 1fr; }
            .th-filters .th-input-wrap:first-child { grid-column: auto; }
            .th-footer { flex-direction: column; align-items: flex-start; }
        }
    </style>

    <div class="th-page space-y-4">

        {{-- Filter Bar --}}
        <div class="th-filters">

            {{-- Search --}}
            <div class="th-input-wrap">
                <span class="th-icon">
                    <x-filament::icon icon="heroicon-o-magnifying-glass" class="h-3.5 w-3.5" />
                </span>
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by user name..."
                    class="th-input"
                >
            </div>

            {{-- User Filter --}}
            @if ($this->canFilterUsers())
                <div class="th-input-wrap" style="max-width:170px;">
                    <span class="th-icon">
                        <x-filament::icon icon="heroicon-o-user" class="h-3.5 w-3.5" />
                    </span>
                    <select wire:model.live="selectedUserId" class="th-select" style="width:100%;">
                        <option value="">All Users</option>
                        @foreach ($this->getUsers() as $filterUser)
                            <option value="{{ $filterUser->id }}">{{ $filterUser->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Period --}}
            <div class="th-input-wrap" style="max-width:150px;">
                <span class="th-icon">
                    <x-filament::icon icon="heroicon-o-calendar" class="h-3.5 w-3.5" />
                </span>
                <select wire:model.live="period" class="th-select" style="width:100%;">
                    @foreach ($this->getPeriodOptions() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Spacer --}}
            <div style="flex:1;"></div>

            {{-- Buttons --}}
            <button type="button" wire:click="exportExcel" class="th-btn-excel">
                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-3.5 w-3.5" />
                Excel
            </button>
            <a
                href="{{ route('user-task-history.pdf', array_filter(['period' => $this->period, 'user' => $this->selectedUserId])) }}"
                target="_blank"
                class="th-btn-pdf"
            >
                <x-filament::icon icon="heroicon-o-document-arrow-down" class="h-3.5 w-3.5" />
                PDF
            </a>
        </div>

        {{-- Table Card --}}
        <div class="th-card">

            @if ($groupedUsers->count())
                @php
                    $reportTasks = $groupedUsers->getCollection()
                        ->flatMap(fn ($user) => $user->historyTasks->map(fn ($task) => ['task' => $task, 'user' => $user]))
                        ->sortBy(fn ($row) => $row['task']->updated_at)
                        ->values();
                @endphp
                <h3 class="th-report-title">{{ match ($this->period) { 'weekly' => 'Last Week Completed Tasks', 'monthly' => 'Monthly Completed Tasks', 'overall' => 'Overall Completed Tasks', default => "Today's Completed Tasks" } }}</h3>
                @php $tasksByEmployee = $reportTasks->groupBy(fn ($row) => $row['user']->id); @endphp
                <table class="th-report-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Work</th>
                            <th>Assigned Time</th>
                            <th class="th-report-estimate">Estimated Time</th>
                            <th class="th-report-description">Description</th>
                            <th>Completion Note</th>
                            <th>Completed Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasksByEmployee as $userTasks)
                            @foreach ($userTasks as $row)
                                @php 
                                    $task = $row['task']; 
                                    $isLastRowForUser = $loop->last;
                                @endphp
                                <tr wire:key="history-task-{{ $task->id }}" class="{{ $isLastRowForUser ? 'th-user-last-row' : '' }}">
                                    @if ($loop->first)
                                        <td class="th-report-employee" rowspan="{{ $userTasks->count() }}">{{ $row['user']->name }}</td>
                                    @endif
                                    <td class="th-report-work">{{ $task->title }}</td>
                                    <td class="th-report-completed">{{ $task->created_at?->timezone('Asia/Karachi')->format('h:i A') }}</td>
                                    <td class="th-report-estimate">{{ $task->estimated_minutes ? $task->estimated_minutes . ' min' : '—' }}</td>
                                    <td class="th-report-description">{{ trim(strip_tags((string) $task->description)) ?: '—' }}</td>
                                    <td>
                                        @if(filled($task->completion_note))
                                            <button type="button" class="th-btn-note" wire:click="openCompletionNote({{ $task->id }})">Read Note</button>
                                        @else
                                            <span class="th-no-note">—</span>
                                        @endif
                                    </td>
                                    <td class="th-report-completed">{{ $task->updated_at?->timezone('Asia/Karachi')->format('h:i A') }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="th-empty">
                    <x-filament::icon icon="heroicon-o-inbox" class="h-10 w-10 mx-auto mb-3" style="color:#E5E7EB;" />
                    <p>No completed tasks for the selected filters.</p>
                </div>
            @endif

            {{-- Footer / Pagination --}}
            @if ($groupedUsers->total())
                <div class="th-footer">
                    <span>Showing {{ $groupedUsers->firstItem() }} to {{ $groupedUsers->lastItem() }} of {{ $groupedUsers->total() }} users</span>
                    <div>{{ $groupedUsers->links() }}</div>
                </div>
            @endif

        </div>

        @if($showCompletionNoteModal)
            <div class="th-note-overlay" wire:click.self="closeCompletionNote">
                <section class="th-note-modal" role="dialog" aria-modal="true">
                    <button type="button" class="th-note-close" wire:click="closeCompletionNote" aria-label="Close">×</button>
                    <h3>Completion Note</h3>
                    <p class="th-note-task">{{ $completionNoteTaskTitle }}</p>
                    <p class="th-note-text">{{ $completionNote }}</p>
                    <button type="button" class="th-btn-note" wire:click="closeCompletionNote">Close</button>
                </section>
            </div>
        @endif
    </div>
</x-filament-panels::page>
