@extends('layouts.employee')

@section('content')
<div class="flex-1 overflow-y-auto bg-[#FAFAFB] p-5 md:p-8">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between gap-4 mb-7">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">All Activities</h1>
                <p class="text-sm text-slate-500 mt-1">A complete record of your task activity.</p>
            </div>
            <a href="{{ route('employee.dashboard') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">Back to Dashboard</a>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="divide-y divide-slate-100">
                @forelse ($activities as $activity)
                    <div class="flex gap-4 p-4 sm:p-5">
                        <div class="w-10 h-10 shrink-0 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="ph-fill ph-check-circle text-xl"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-800 break-words">{{ $activity->description ?? ucfirst($activity->action) }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $activity->created_at->timezone('Asia/Karachi')->format('M d, Y h:i A') }} · {{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center text-sm text-slate-500">No activities found.</div>
                @endforelse
            </div>
            @if ($activities->hasPages())
                <div class="p-4 border-t border-slate-100">{{ $activities->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
