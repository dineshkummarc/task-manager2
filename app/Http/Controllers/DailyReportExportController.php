<?php

namespace App\Http\Controllers;

use App\Models\DailyReportEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyReportExportController extends Controller
{
    public function pdf(Request $request): View
    {
        abort_unless($request->user()?->isAdmin() || $request->user()?->isManager(), 403);
        $data = $request->validate(['date' => ['nullable', 'date'], 'user' => ['nullable', 'integer', 'exists:users,id'], 'project' => ['nullable', 'integer', 'exists:projects,id']]);
        $entries = DailyReportEntry::query()->with(['user', 'project'])
            ->when($data['date'] ?? null, fn (Builder $q, string $date) => $q->whereDate('report_date', $date))
            ->when($data['user'] ?? null, fn (Builder $q, int $user) => $q->where('user_id', $user))
            ->when($data['project'] ?? null, fn (Builder $q, int $project) => $q->where('project_id', $project))
            ->orderByDesc('report_date')->orderBy('user_id')->orderBy('slot_start')->get();

        return view('filament.pages.daily-reports-pdf', compact('entries'));
    }
}
