@extends('layouts.employee')

@section('content')
<div class="flex-1 overflow-y-auto bg-[#FAFAFB] p-6 lg:p-8">
    
    <!-- Top Bar (Desktop) -->
    <div class="hidden md:flex justify-between items-center mb-8">
        <div class="relative w-96">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">All Tasks</h1>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="relative group">
                <div class="flex items-center gap-3 pl-4 border-l border-slate-200 cursor-pointer">
                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff' }}" alt="Profile" class="w-10 h-10 rounded-full shadow-sm object-cover">
                    <div class="hidden lg:block">
                        <p class="text-sm font-bold text-slate-800 leading-tight">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500">{{ ucfirst($user->role) ?? 'Employee' }}</p>
                    </div>
                </div>
                
                <!-- Dropdown -->
                <div class="absolute right-0 top-full mt-2 w-64 bg-white rounded-xl shadow-lg border border-slate-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 p-4">
                    <div class="flex flex-col items-center pb-4 border-b border-slate-100">
                        <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff' }}" class="w-16 h-16 rounded-full mb-2 border-2 border-indigo-100 shadow-sm object-cover">
                        <h3 class="font-bold text-slate-800">{{ $user->name }}</h3>
                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                    </div>
                    <div class="pt-2 border-t border-slate-100">
                         <form method="POST" action="{{ route('employee.logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-2 py-2 rounded-lg text-rose-500 hover:bg-rose-50 font-medium transition-all-fast text-sm">
                                <i class="ph ph-sign-out text-lg"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
            <h3 class="font-bold text-slate-800 text-lg">Your Active Tasks</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-4">Task Name</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Due Date</th>
                        <th class="px-6 py-4">Estimated Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tasks as $task)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-slate-800">{{ $task->title }}</p>
                            @if($task->description)
                                <p class="text-xs text-slate-500 mt-1 line-clamp-1">{{ $task->description }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($task->is_blocked)
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-rose-100 text-rose-700">Blocked</span>
                            @elseif($task->status === 'pending')
                                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">Pending</span>
                            @elseif($task->status === 'in_progress')
                                <span class="text-xs font-semibold text-indigo-600">In Progress</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($task->due_date)
                                <span class="{{ $task->due_date < now() ? 'text-rose-600 font-semibold' : 'text-slate-600' }}">
                                    {{ $task->due_date->format('M d, Y h:i A') }}
                                </span>
                            @else
                                <span class="text-slate-400">No deadline</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($task->estimated_minutes)
                                {{ $task->estimated_minutes }} mins
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                            No active tasks found. You're all caught up!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($tasks->hasPages())
        <div class="px-6 py-4 border-t border-slate-200">
            {{ $tasks->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
