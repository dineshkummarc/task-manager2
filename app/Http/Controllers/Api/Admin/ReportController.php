<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\DailyReportEntryResource;
use App\Http\Resources\PerformanceResource;
use App\Models\DailyReportEntry;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        $v = $request->validate(['date' => ['nullable', 'date'], 'employee_id' => ['nullable', 'exists:users,id'], 'project_id' => ['nullable', 'exists:projects,id'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);
        $entries = DailyReportEntry::with(['user', 'project'])->when($v['date'] ?? null, fn ($q, $d) => $q->whereDate('report_date', $d))->when($v['employee_id'] ?? null, fn ($q, $id) => $q->where('user_id', $id))->when($v['project_id'] ?? null, fn ($q, $id) => $q->where('project_id', $id))->orderByDesc('report_date')->orderBy('slot_start')->paginate($v['per_page'] ?? 25);

        return DailyReportEntryResource::collection($entries);
    }

    public function performance(Request $request)
    {
        $v = $request->validate(['project_id' => ['nullable', 'exists:projects,id'], 'date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date', 'after_or_equal:date_from'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100']]);
        $users = User::with('project')->withCount(['assignedTasks as completed_tasks_count' => fn ($q) => $q->where('status', 'completed')->when($v['date_from'] ?? null, fn ($q, $d) => $q->whereDate('updated_at', '>=', $d))->when($v['date_to'] ?? null, fn ($q, $d) => $q->whereDate('updated_at', '<=', $d))])->when($v['project_id'] ?? null, fn ($q, $id) => $q->where('project_id', $id))->orderByDesc('completed_tasks_count')->paginate($v['per_page'] ?? 25);

        return PerformanceResource::collection($users);
    }
}
