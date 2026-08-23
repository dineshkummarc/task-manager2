<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $user?->name ?? 'All Users' }} — Employee Reports</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; background: #fff; color: #0f172a; font-family: Arial, sans-serif; }
        
        .print { margin-bottom: 22px; padding: 8px 14px; border: 0; border-radius: 6px; background: #0f766e; color: #fff; font-weight: 700; cursor: pointer; }
        
        .report-header { text-align: center; margin: 0 0 20px; }
        .report-name { margin: 0; font-size: 24px; font-weight: 700; }
        .report-email { margin: 5px 0 3px; color: #64748b; font-size: 13px; }
        .report-period { margin: 0; color: #94a3b8; font-size: 12px; }

        /* Report Table Styling matching the Filament View */
        .th-report-table { width: 100%; border-collapse: collapse; border: 1px solid #E5E7EB; border-radius: 10px; overflow: hidden; }
        .th-report-table th { padding: 12px 16px; background: #F8FAFC; border-bottom: 1px solid #E5E7EB; color: #64748B; font-size: 11.5px; font-weight: 700; text-align: left; text-transform: uppercase; }
        .th-report-table td { padding: 12px 16px; border-bottom: 1px solid #EEF2F7; color: #334155; font-size: 13px; vertical-align: middle; }
        
        /* Grouping & Borders */
        .th-report-table tr.th-user-last-row td { border-bottom: 3px solid #94A3B8 !important; }
        .th-report-table tr:last-child td { border-bottom: none; }
        .th-report-table tr:nth-child(odd) td { background: #FCFDFE; }
        
        .th-report-employee { width: 140px; border-bottom: 3px solid #94A3B8 !important; border-right: 1px solid #E5E7EB; background: #F5F8FF !important; color: #334155 !important; font-size: 14px; font-weight: 700; text-align: center; vertical-align: middle !important; }
        .th-report-work { min-width: 140px; color: #0F172A !important; font-size: 13.5px; font-weight: 600; }
        .th-report-estimate { width: 120px; white-space: nowrap; font-size: 12.5px; color: #475569; }
        .th-report-description { color: #64748B !important; font-size: 12.5px; max-width: 200px; word-break: break-word; }
        .th-report-completed { width: 120px; color: #15803D !important; font-size: 13px; font-weight: 700; white-space: nowrap; }

        .empty { padding: 35px; text-align: center; color: #94a3b8; font-size: 13px; border: 1px solid #e2e8f0; border-radius: 10px; }

        @media print {
            body { padding: 0; }
            .print { display: none; }
            @page { margin: 12mm; }
        }
    </style>
</head>
<body>
    <button class="print" onclick="window.print()">Print / Save as PDF</button>
    
    <div class="report-header">
        <h1 class="report-name">{{ $user?->name ?? 'All Users' }}</h1>
        <p class="report-email">{{ $user?->email ?? 'Employee task report summary' }}</p>
        <p class="report-period">{{ $periodLabel ?? 'Report Period' }} &bull; {{ $tasks->count() }} completed tasks</p>
    </div>

    @if($tasks->count() > 0)
        @php
            // Group tasks by employee ID if multiple users are present, or group under a single collection
            $sortedTasks = $tasks->sortBy('updated_at');
            $tasksByEmployee = $sortedTasks->groupBy(fn ($task) => $task->user_id ?? $task->assignee_id ?? 'general');
        @endphp

        <table class="th-report-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Work</th>
                    <th>Assigned Time</th>
                    <th>Estimated Time</th>
                    <th>Description</th>
                    <th>Completed Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tasksByEmployee as $userTasks)
                    @foreach ($userTasks as $task)
                        @php 
                            $isLastRowForUser = $loop->last;
                            // Retrieve the user/assignee model dynamically
                            $employeeName = $task->user?->name ?? $task->assignee?->name ?? $user?->name ?? 'Team Member';
                        @endphp
                        <tr class="{{ $isLastRowForUser ? 'th-user-last-row' : '' }}">
                            @if ($loop->first)
                                <td class="th-report-employee" rowspan="{{ $userTasks->count() }}">{{ $employeeName }}</td>
                            @endif
                            <td class="th-report-work">{{ $task->title }}</td>
                            <td class="th-report-completed">{{ $task->created_at?->timezone('Asia/Karachi')->format('h:i A') }}</td>
                            <td class="th-report-estimate">{{ $task->estimated_minutes ? $task->estimated_minutes . ' min' : '—' }}</td>
                            <td class="th-report-description">{{ trim(strip_tags((string) $task->description)) ?: '—' }}</td>
                            <td class="th-report-completed">{{ $task->updated_at?->timezone('Asia/Karachi')->format('h:i A') }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">No completed tasks for the selected filters.</div>
    @endif
</body>
</html>
