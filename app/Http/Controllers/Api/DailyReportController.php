<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\DailyReportEntryRequest;
use App\Http\Resources\DailyReportEntryResource;
use App\Models\DailyReportEntry;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DailyReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $entries = DailyReportEntry::query()
            ->where('user_id', $request->user()->id)
            ->with('project')
            ->when($validated['date'] ?? null, fn ($query, string $date) => $query->whereDate('report_date', $date))
            ->orderByDesc('report_date')
            ->orderBy('slot_start')
            ->paginate($validated['per_page'] ?? 20);

        return DailyReportEntryResource::collection($entries);
    }

    public function show(Request $request, DailyReportEntry $dailyReport)
    {
        $this->ensureOwnership($request, $dailyReport);

        return new DailyReportEntryResource($dailyReport->load('project'));
    }

    public function store(DailyReportEntryRequest $request)
    {
        $validated = $request->validated();
        $reportDate = Carbon::parse($validated['report_date'])->toDateString();

        abort_if(DailyReportEntry::query()
            ->where('user_id', $request->user()->id)
            ->whereDate('report_date', $reportDate)
            ->where('slot_start', $validated['slot_start'])
            ->exists(), 422, 'A report entry already exists for this time slot.');

        $entry = DailyReportEntry::create([
            ...$validated,
            'report_date' => $reportDate,
            'slot_label' => $this->slotLabel($validated['slot_start']),
            'user_id' => $request->user()->id,
        ]);

        $this->logActivity($request, 'created daily report entry', $entry);

        return (new DailyReportEntryResource($entry->load('project')))->response()->setStatusCode(201);
    }

    public function update(DailyReportEntryRequest $request, DailyReportEntry $dailyReport)
    {
        $this->ensureOwnership($request, $dailyReport);
        $validated = $request->validated();
        $reportDate = Carbon::parse($validated['report_date'])->toDateString();

        abort_if(DailyReportEntry::query()
            ->where('user_id', $request->user()->id)
            ->whereDate('report_date', $reportDate)
            ->where('slot_start', $validated['slot_start'])
            ->whereKeyNot($dailyReport->id)
            ->exists(), 422, 'A report entry already exists for this time slot.');

        $dailyReport->update([
            ...$validated,
            'report_date' => $reportDate,
            'slot_label' => $this->slotLabel($validated['slot_start']),
        ]);

        $this->logActivity($request, 'updated daily report entry', $dailyReport);

        return new DailyReportEntryResource($dailyReport->fresh()->load('project'));
    }

    public function destroy(Request $request, DailyReportEntry $dailyReport)
    {
        $this->ensureOwnership($request, $dailyReport);
        $title = $dailyReport->task_title ?: $dailyReport->slot_label;
        $dailyReport->delete();

        UserActivity::create([
            'user_id' => $request->user()->id,
            'action' => 'deleted daily report entry',
            'description' => 'Deleted daily report entry: '.$title,
        ]);

        return response()->noContent();
    }

    private function ensureOwnership(Request $request, DailyReportEntry $dailyReport): void
    {
        abort_unless((int) $dailyReport->user_id === (int) $request->user()->id, 403);
    }

    private function slotLabel(string $slotStart): string
    {
        $start = Carbon::createFromFormat('H:i:s', $slotStart);

        return $start->format('g:i A').' - '.$start->copy()->addHour()->format('g:i A');
    }

    private function logActivity(Request $request, string $action, DailyReportEntry $entry): void
    {
        UserActivity::create([
            'user_id' => $request->user()->id,
            'action' => $action,
            'description' => 'Daily report entry: '.($entry->task_title ?: $entry->slot_label),
        ]);
    }
}
