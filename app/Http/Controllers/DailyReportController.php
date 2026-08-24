<?php

namespace App\Http\Controllers;

use App\Models\DailyReportEntry;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DailyReportController extends Controller
{
    public function create(Request $request): View|JsonResponse
    {
        $reportDate = Carbon::parse($request->query('date', today()->toDateString()))->toDateString();
        $slots = $this->slots();
        $entries = DailyReportEntry::query()
            ->where('user_id', $request->user()->id)
            ->whereDate('report_date', $reportDate)
            ->get()
            ->keyBy('slot_start');

        if ($request->expectsJson()) {
            return response()->json([
                'report_date' => $reportDate,
                'entries' => $entries->map(fn (DailyReportEntry $entry) => [
                    'task_title' => $entry->task_title,
                    'description' => $entry->description,
                    'project_id' => $entry->project_id,
                ]),
            ]);
        }

        return view('employee.daily-report', [
            'user' => $request->user(),
            'reportDate' => $reportDate,
            'slots' => $slots,
            'entries' => $entries,
            'projects' => Project::query()
                ->whereIn('name', ['BPOVO', 'GITMIT'])
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'report_date' => ['required', 'date'],
            'entries' => ['required', 'array', 'size:9'],
            'entries.*.task_title' => ['nullable', 'string', 'max:255'],
            'entries.*.description' => ['nullable', 'string', 'max:5000'],
            'entries.*.project_id' => ['nullable', 'integer', 'exists:projects,id'],
        ]);

        $reportDate = Carbon::parse($validated['report_date'])->toDateString();
        $slots = $this->slots();
        $allowedProjectIds = Project::query()->whereIn('name', ['BPOVO', 'GITMIT'])->pluck('id')->all();

        foreach ($validated['entries'] as $entry) {
            if (! empty($entry['project_id']) && ! in_array((int) $entry['project_id'], $allowedProjectIds, true)) {
                return back()->withErrors(['entries' => 'Please select BPOVO or GITMIT as the project.'])->withInput();
            }
        }

        DB::transaction(function () use ($request, $validated, $reportDate, $slots): void {
            foreach ($slots as $index => $slot) {
                $entry = $validated['entries'][$index];

                DailyReportEntry::updateOrCreate(
                    [
                        'user_id' => $request->user()->id,
                        'report_date' => $reportDate,
                        'slot_start' => $slot['start'],
                    ],
                    [
                        'slot_label' => $slot['label'],
                        'task_title' => $entry['task_title'] ?: null,
                        'description' => $entry['description'] ?: null,
                        'project_id' => $entry['project_id'] ?: null,
                    ],
                );
            }
        });

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Daily report saved successfully.']);
        }

        return redirect()->route('employee.daily-report.create', ['date' => $reportDate])
            ->with('success', 'Daily report saved successfully.');
    }

    private function slots(): array
    {
        return collect(range(0, 8))->map(function (int $offset): array {
            $start = Carbon::createFromTime(9, 30)->addHours($offset);
            $end = (clone $start)->addHour();

            return [
                'start' => $start->format('H:i:s'),
                'label' => $start->format('g:i A').' - '.$end->format('g:i A'),
            ];
        })->all();
    }
}
