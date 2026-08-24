@extends('layouts.employee')

@section('content')
<div class="flex-1 overflow-y-auto bg-[#FAFAFB] p-4 sm:p-6 lg:p-8">
    <div class="mx-auto max-w-7xl">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Daily Report</h1>
                <p class="mt-1 text-sm text-slate-500">Plan and record your work for each hourly time slot.</p>
            </div>
            <div class="flex items-center gap-2">
                <label for="date" class="sr-only">Report date</label>
                <input id="date" type="date" value="{{ $reportDate }}" data-daily-report-date data-report-url="{{ route('employee.daily-report.create') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('employee.daily-report.store') }}" data-async-form data-require-report-entry>
            @csrf
            <input type="hidden" name="report_date" value="{{ $reportDate }}">

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-[850px] w-full text-left">
                        <thead class="bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-5 py-4">Time Slot</th>
                                <th class="px-5 py-4">Task Title</th>
                                <th class="px-5 py-4">Description</th>
                                <th class="px-5 py-4">Project</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($slots as $index => $slot)
                                @php($entry = $entries->get($slot['start']))
                                <tr class="align-top" data-slot-start="{{ $slot['start'] }}">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-700">{{ $slot['label'] }}</td>
                                    <td class="px-5 py-3"><input name="entries[{{ $index }}][task_title]" value="{{ old("entries.$index.task_title", $entry?->task_title) }}" maxlength="255" placeholder="Task title" class="w-full min-w-48 rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"></td>
                                    <td class="px-5 py-3"><textarea name="entries[{{ $index }}][description]" rows="2" maxlength="5000" placeholder="What did you work on?" class="w-full min-w-72 rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old("entries.$index.description", $entry?->description) }}</textarea></td>
                                    <td class="px-5 py-3"><select name="entries[{{ $index }}][project_id]" class="w-full min-w-32 rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500"><option value="">Select project</option>@foreach ($projects as $project)<option value="{{ $project->id }}" @selected((string) old("entries.$index.project_id", $entry?->project_id) === (string) $project->id)>{{ ucfirst(strtolower($project->name)) }}</option>@endforeach</select></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-5 flex justify-end"><button type="submit" data-submit-button data-loading-text="Saving..." class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700"><i class="ph ph-floppy-disk text-lg"></i> <span data-submit-label>Save Daily Report</span></button></div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.querySelector('[data-daily-report-date]');
    const reportForm = document.querySelector('[data-require-report-entry]');
    dateInput?.addEventListener('change', async () => {
        if (!dateInput.value) return;
        dateInput.disabled = true;
        try {
            const response = await fetch(`${dateInput.dataset.reportUrl}?date=${encodeURIComponent(dateInput.value)}`, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error('Unable to load this report date.');
            const data = await response.json();
            reportForm.querySelector('[name="report_date"]').value = data.report_date;
            reportForm.querySelectorAll('[data-slot-start]').forEach((row) => {
                const entry = data.entries[row.dataset.slotStart] || {};
                row.querySelector('[name$="[task_title]"]').value = entry.task_title || '';
                row.querySelector('[name$="[description]"]').value = entry.description || '';
                row.querySelector('[name$="[project_id]"]').value = entry.project_id || '';
            });
            window.history.replaceState({}, '', `${window.location.pathname}?date=${encodeURIComponent(data.report_date)}`);
        } catch (error) {
            window.dispatchEvent(new CustomEvent('employee-toast', { detail: { message: error.message, isError: true } }));
        } finally {
            dateInput.disabled = false;
        }
    });
});
</script>
@endsection
