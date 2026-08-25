<x-filament-widgets::widget>
    <div x-data="{ isFullscreen: false, zoomLevel: 100 }" class="rounded-xl bg-white transition-all flex flex-col" :class="isFullscreen ? 'fixed inset-4 z-50 shadow-2xl h-[calc(100vh-2rem)]' : ''" style="border:1px solid #e2e8f0;">
        
        <!-- Header: Ek hi line mein sab kuch rakhne ke liye flex-nowrap aur overflow-x-auto -->
        <div class="tsm-header flex items-center justify-between gap-3 px-5 py-4" style="border-bottom:1px solid #e2e8f0;">
            
            <!-- Left Side: Title, Zoom & Fullscreen -->
            <div class="flex items-center gap-2 shrink-0">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white whitespace-nowrap">{{ $heading }}</h2>
                <div class="tsm-zoom">
                    <button type="button" @click="zoomLevel=Math.max(75,zoomLevel-10)" aria-label="Zoom out">−</button>
                    <span x-text="zoomLevel+'%'"></span>
                    <button type="button" @click="zoomLevel=Math.min(125,zoomLevel+10)" aria-label="Zoom in">+</button>
                </div>
                <button type="button" @click="isFullscreen=!isFullscreen" class="p-1.5 border rounded-md hover:bg-gray-100 text-gray-600 shadow-sm ml-1" aria-label="Toggle full screen">
                    <x-heroicon-m-arrows-pointing-out class="w-4 h-4" x-show="!isFullscreen" />
                    <x-heroicon-m-x-mark class="w-4 h-4" x-show="isFullscreen" style="display:none;" />
                </button>
            </div>

            <!-- Right Side Controls: Ek hi line mein fix kiye gaye hain -->
            <div class="tsm-controls shrink-0">
                <div class="inline-flex rounded-lg p-1 shrink-0" style="background:#f1f5f9;">
                    @foreach (['daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly'] as $mode=>$label)
                        <button type="button" wire:click="setViewMode('{{ $mode }}')" class="rounded-md px-3 py-1.5 text-xs font-semibold whitespace-nowrap" style="{{ $viewMode===$mode ? 'background:#fff;color:#4338ca;box-shadow:0 1px 2px rgba(0,0,0,.08);' : 'color:#64748b;' }}">{{ $label }}</button>
                    @endforeach
                </div>
                <label class="tsm-field">
                    <span>Status</span>
                    <select wire:change="setSelectedStatus($event.target.value)" class="tsm-select">
                        <option value="all">All Statuses</option>
                        <option value="completed" @selected($selectedStatus==='completed')>Completed</option>
                        <option value="pending" @selected($selectedStatus==='pending')>Pending</option>
                        <option value="unresolved" @selected($selectedStatus==='unresolved')>Unresolved</option>
                        <option value="in_progress" @selected($selectedStatus==='in_progress')>In Process</option>
                    </select>
                </label>
                <label class="tsm-field">
                    <span>User</span>
                    <select wire:change="setSelectedUser($event.target.value)" class="tsm-select">
                        <option value="">All Users</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" @selected($selectedUserId===$employee->id)>{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="tsm-field">
                    <span>Date</span>
                    <input type="date" max="{{ $maxDate }}" value="{{ $selectedDate }}" wire:change="setDate($event.target.value)">
                </label>
            </div>
        </div>

        <!-- Table / Schedule Content (Neeche properly show hoga) -->
        <div class="tsm-scroll" :style="isFullscreen ? 'max-height:none;flex:1;' : ''" wire:key="schedule-list-{{ $viewMode }}-{{ $selectedDate }}-{{ $selectedUserId??'all' }}-{{ $selectedStatus }}">
            <div class="tsm-schedule" :style="`zoom:${zoomLevel/100};`">
                @foreach($scheduleRows as $row)
                    <div class="tsm-schedule-row">
                        <div class="tsm-time">{{ $row['label'] }}</div>
                        <div class="tsm-slot">
                            @foreach($row['items'] as $item)
                                @php
                                    $status=$item['status']==='blockage'?'unresolved':$item['status'];
                                    $statusLabel=match($status){'completed'=>'Completed','in_progress'=>'In Process','unresolved'=>'Unresolved',default=>'Pending'};
                                @endphp
                                <div class="tsm-task tsm-task-{{ $status }}">
                                    <button type="button" class="tsm-task-main" wire:click="viewUserReport({{ $item['userId'] }})" title="Open {{ $item['userName'] }}'s employee report">
                                    <span>{{ $item['title'] }}</span>
                                    @if(! $selectedUserId)<small>{{ $item['userName'] }}</small>@endif
                                    <strong>{{ $statusLabel }}</strong>
                                    </button>
                                    @if($status === 'completed' && filled($item['completion_note']))
                                        <button type="button" class="tsm-read-note" wire:click="openCompletionNote({{ $item['id'] }})">Read Note</button>
                                    @endif
                                    @if($status === 'unresolved' && filled($item['blockage_text']))
                                        <button type="button" class="tsm-read-note" wire:click="openBlockageNote({{ $item['id'] }})">Read Note</button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if($showCompletionNoteModal)
        <div class="tsm-note-overlay" wire:click.self="closeCompletionNote">
            <section class="tsm-note-modal" role="dialog" aria-modal="true">
                <button type="button" class="tsm-note-close" wire:click="closeCompletionNote" aria-label="Close">×</button>
                <h3>{{ $noteHeading }}</h3>
                <p class="tsm-note-task">{{ $completionNoteTaskTitle }}</p>
                <p class="tsm-note-text">{{ $completionNote }}</p>
                <button type="button" class="tsm-note-done" wire:click="closeCompletionNote">Close</button>
            </section>
        </div>
    @endif

    <style>
        .tsm-controls {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            gap: 0.6rem;
        }
        .tsm-field {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }
        .tsm-field span {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #64748b;
        }
        .tsm-field input, .tsm-select {
            width: 130px; /* Width thori compact ki hai taake aik line mein fit aa jayein */
            border: 1px solid #cbd5e1;
            border-radius: .5rem;
            background: #fff;
            padding: .4rem .6rem;
            color: #0f172a;
            font-size: 12px;
            font-weight: 600;
        }
        .tsm-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: none !important;
            padding-right: .6rem;
        }
        .tsm-zoom {
            display: flex;
            align-items: center;
            height: 34px;
            margin-left: .3rem;
            border: 1px solid #dbe3ee;
            border-radius: 6px;
            background: #f8fafc;
            color: #64748b;
            font-size: 16px;
            font-weight: 600;
        }
        .tsm-zoom button {
            width: 26px;
            height: 100%;
            border: 0;
            background: transparent;
            color: #64748b;
            font-size: 16px;
            line-height: 1;
            cursor: pointer;
        }
        .tsm-zoom button:hover {
            background: #eef2f7;
        }
        .tsm-zoom span {
            min-width: 38px;
            text-align: center;
            font-size: 12px;
            font-weight: 700;
        }
        .tsm-scroll {
            overflow: auto;
            background: #fff;
        }
        .tsm-schedule {
            min-width: 650px;
        }
        .tsm-schedule-row {
            display: grid;
            grid-template-columns: 108px minmax(0,1fr);
            min-height: 28px;
            border-bottom: 1px solid #e2e8f0;
        }
        .tsm-time {
            display: flex;
            align-items: center;
            padding: 0 12px;
            color: #0f172a;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        .tsm-slot {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 3px;
            min-height: 28px;
            padding: 2px 10px;
            border-left: 1px solid #f1f5f9;
        }
        .tsm-task {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            border: 1px solid transparent;
            dir: ltr;
            border-radius: 5px;
            padding: 4px 10px;
            background: #fff;
            color: #1e293b;
            font-size: 12px;
            font-weight: 600;
            text-align: left;
        }
        .tsm-task-main { align-items: center; background: transparent; border: 0; color: inherit; cursor: pointer; display: flex; font: inherit; gap: 10px; min-width: 0; padding: 0; text-align: left; width: 100%; }
        .tsm-task span {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .tsm-task small {
            margin-left: auto;
            color: #64748b;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .tsm-task strong {
            margin-left: auto;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }
        .tsm-read-note { background: #2563eb; border: 0; border-radius: 4px; color: #fff; cursor: pointer; font-size: 10px; font-weight: 700; padding: 3px 7px; white-space: nowrap; }
        .tsm-read-note:hover { background: #1d4ed8; }
        .tsm-task:hover {
            filter: brightness(.98);
        }
        .tsm-task-completed { background: #ecfdf3; border-color: #bbf7d0; color: #15803d; }
        .tsm-task-in_progress { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
        .tsm-task-pending { background: #fffdf5; border-color: #fef3c7; color: #92400e; }
        .tsm-task-unresolved { background: #fff7f7; border-color: #fecaca; color: #b91c1c; }
        .tsm-note-overlay { align-items: center; background: rgba(15, 23, 42, .45); display: flex; inset: 0; justify-content: center; padding: 1rem; position: fixed; z-index: 100; }
        .tsm-note-modal { background: #fff; border-radius: 12px; box-shadow: 0 20px 45px rgba(15, 23, 42, .25); max-width: 440px; padding: 24px; position: relative; width: 100%; }
        .tsm-note-modal h3 { color: #0f172a; font-size: 18px; font-weight: 800; margin: 0; }
        .tsm-note-task { color: #64748b; font-size: 13px; font-weight: 600; margin: 4px 0 16px; }
        .tsm-note-text { background: #f8fafc; border-radius: 8px; color: #334155; line-height: 1.6; margin: 0; padding: 12px; white-space: pre-wrap; }
        .tsm-note-close { background: transparent; border: 0; color: #94a3b8; cursor: pointer; font-size: 24px; position: absolute; right: 13px; top: 9px; }
        .tsm-note-done { background: #2563eb; border: 0; border-radius: 7px; color: #fff; cursor: pointer; font-size: 12px; font-weight: 700; margin-top: 16px; padding: 8px 14px; }

        @media (max-width: 640px) {
            .tsm-header {
                align-items: stretch;
                flex-direction: column;
                padding: .75rem;
            }
            .tsm-header > div:first-child {
                justify-content: space-between;
            }
            .tsm-controls {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .5rem;
                width: 100%;
            }
            .tsm-controls > div:first-child,
            .tsm-controls .tsm-field:last-child {
                grid-column: 1 / -1;
            }
            .tsm-controls > div:first-child {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .tsm-controls > div:first-child button {
                padding-inline: .35rem;
            }
            .tsm-field {
                min-width: 0;
            }
            .tsm-field input,
            .tsm-select {
                box-sizing: border-box;
                min-width: 0;
                width: 100%;
            }
        }
    </style>
</x-filament-widgets::widget>
