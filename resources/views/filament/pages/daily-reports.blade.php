<x-filament-panels::page>
    @php($users = $this->getUsers())

    <style>
        .daily-report-page { font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        .daily-report-toolbar { align-items: end; display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        .daily-report-field { display: block; min-width: 175px; }
        .daily-report-field label { color: #334155; display: block; font-size: 12px; font-weight: 700; margin: 0 0 6px; }
        .daily-report-control { appearance: auto; background: #fff; border: 1px solid #d8e0eb; border-radius: 10px; box-sizing: border-box; color: #334155; font-size: 13px; height: 41px; outline: none; padding: 0 12px; width: 100%; }
        select.daily-report-control { -webkit-appearance: none !important; -moz-appearance: none !important; appearance: none !important; background-image: none !important; padding-right: 36px; }
        .daily-report-select { display: block; position: relative; }
        .daily-report-select::after { border-bottom: 2px solid #64748b; border-right: 2px solid #64748b; content: ''; height: 7px; pointer-events: none; position: absolute; right: 15px; top: 14px; transform: rotate(45deg); width: 7px; }
        .daily-report-control:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, .12); }
        .daily-report-export { align-items: center; border: 0; border-radius: 10px; box-sizing: border-box; color: #fff !important; cursor: pointer; display: inline-flex; font-size: 13px; font-weight: 700; gap: 7px; height: 41px; justify-content: center; min-width: 84px; padding: 0 15px; text-decoration: none !important; }
        .daily-report-export:hover { color: #fff !important; filter: brightness(.95); }
        .daily-report-pdf { background: #e91e4d !important; } .daily-report-excel { background: #059669 !important; }
        .daily-report-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 2px 7px rgba(15, 23, 42, .04); overflow: hidden; }
        .daily-report-table { border-collapse: collapse; min-width: 780px; table-layout: fixed; width: 100%; }
        .daily-report-table th { background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #587096; font-size: 11px; font-weight: 800; letter-spacing: .02em; padding: 15px 18px; text-align: left; text-transform: uppercase; }
        .daily-report-table td { border-bottom: 1px solid #edf1f5; color: #1e293b; font-size: 14px; padding: 14px 18px; vertical-align: middle; }
        .daily-report-table tr:last-child td { border-bottom: 0; }
        .daily-report-user-button { align-items: center; background: transparent; border: 0; color: #0f172a; cursor: pointer; display: inline-flex; font-size: 14px; font-weight: 750; gap: 9px; padding: 0; text-align: left; }
        .daily-report-chevron { color: #64748b; transition: transform .18s ease; width: 15px; } .daily-report-chevron.is-open { transform: rotate(90deg); }
        .daily-report-count { background: #eef2ff; border-radius: 999px; color: #4f46e5; display: inline-block; font-size: 11px; font-weight: 800; min-width: 28px; padding: 4px 8px; text-align: center; }
        .daily-report-details td { background: #f8fafc; padding: 0; }
        .daily-report-details-inner { padding: 16px 18px 18px 50px; }
        .daily-report-details-title { color: #475569; font-size: 12px; font-weight: 800; margin: 0 0 10px; text-transform: uppercase; }
        .daily-report-slot-table { background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; border-collapse: separate; border-spacing: 0; overflow: hidden; width: 100%; }
        .daily-report-slot-table th { background: #f8fafc; color: #64748b; font-size: 10px; font-weight: 800; padding: 10px 12px; text-align: left; text-transform: uppercase; }
        .daily-report-slot-table td { background: #fff; border-top: 1px solid #e8edf3; color: #334155; font-size: 13px; padding: 11px 12px; vertical-align: top; }
        .daily-report-project { background: #eef2ff; border: 1px solid #dbe4ff; border-radius: 6px; color: #4f46e5; display: inline-block; font-size: 11px; font-weight: 800; padding: 3px 7px; }
        .daily-report-empty { color: #94a3b8; padding: 46px 18px !important; text-align: center; }
        @media (max-width: 640px) { .daily-report-field { min-width: 100%; } .daily-report-export { flex: 1; } .daily-report-details-inner { padding-left: 14px; } }
    </style>

    <div class="daily-report-page">
        <div class="daily-report-toolbar">
            <div class="daily-report-field">
                <label for="daily-report-date">Date</label>
                <input id="daily-report-date" type="date" wire:model.live="selectedDate" class="daily-report-control">
            </div>
            <div class="daily-report-field">
                <label for="daily-report-employee">Employee</label>
                <span class="daily-report-select"><select id="daily-report-employee" wire:model.live="selectedUserId" class="daily-report-control">
                        <option value="">All Users</option>
                        @foreach ($this->getAllEmployees() as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select></span>
            </div>
            <div class="daily-report-field">
                <label for="daily-report-project">Project</label>
                <span class="daily-report-select"><select id="daily-report-project" wire:model.live="selectedProjectId" class="daily-report-control">
                        <option value="">All Projects</option>
                        @foreach ($this->getProjects() as $project)
                            <option value="{{ $project->id }}">{{ ucfirst(strtolower($project->name)) }}</option>
                        @endforeach
                    </select></span>
            </div>
            <div style="flex: 1"></div>
            <a href="{{ $this->getPdfUrl() }}" target="_blank" class="daily-report-export daily-report-pdf">PDF</a>
            <button type="button" wire:click="exportExcel" class="daily-report-export daily-report-excel">Excel</button>
        </div>

        <div class="daily-report-card">
            <div class="overflow-x-auto">
                <table class="daily-report-table">
                    <thead>
                        <tr>
                            <th style="width: 34%">Employee</th>
                            <th style="width: 31%">Email</th>
                            <th style="width: 20%">Report Date</th>
                            <th style="width: 15%; text-align: right">Entries</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $employee)
                            @php($isOpen = $expandedUsers[$employee->id] ?? false)
                            @php($entries = $isOpen ? $this->getUserEntries($employee->id) : collect())
                            @php($entryCount = $employee->report_entries_count ?: $employee->completed_tasks_count)
                            <tr wire:click="toggleUser({{ $employee->id }})" style="cursor: pointer">
                                <td>
                                    <span class="daily-report-user-button">
                                        <x-heroicon-m-chevron-right class="daily-report-chevron {{ $isOpen ? 'is-open' : '' }}" />
                                        {{ $employee->name }}
                                    </span>
                                </td>
                                <td>{{ $employee->email }}</td>
                                <td>{{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</td>
                                <td style="text-align: right"><span class="daily-report-count">{{ $entryCount }}</span></td>
                            </tr>
                            @if ($isOpen)
                                <tr class="daily-report-details">
                                    <td colspan="4">
                                        <div class="daily-report-details-inner">
                                            <p class="daily-report-details-title">{{ $employee->name }}'s Daily Report & Completed Tasks</p>
                                            <div class="overflow-x-auto">
                                                <table class="daily-report-slot-table">
                                                    <thead><tr><th>Time Slot</th><th>Task Title</th><th>Description</th><th>Project</th></tr></thead>
                                                    <tbody>
                                                        @forelse ($entries as $entry)
                                                            <tr>
                                                                <td class="whitespace-nowrap font-semibold">{{ $entry->slot_label }}</td>
                                                                <td>{{ $entry->task_title ?: '—' }}</td>
                                                                <td class="max-w-lg whitespace-pre-line">{{ $entry->description ?: '—' }}</td>
                                                                <td>@if($entry->project)<span class="daily-report-project">{{ $entry->project->name }}</span>@else — @endif</td>
                                                            </tr>
                                                        @empty
                                                            <tr><td colspan="4" class="text-center text-slate-500">No daily report entries match the selected filters.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="4" class="daily-report-empty">No daily reports found for the selected filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
