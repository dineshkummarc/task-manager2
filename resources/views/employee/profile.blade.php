@extends('layouts.employee')

@section('content')
<div class="flex-1 overflow-y-auto bg-[#FAFAFB] p-4 sm:p-6 lg:p-8">
    
    <!-- Top Bar (Desktop) -->
    <div class="hidden md:flex justify-between items-center mb-8">
        <div class="relative w-96">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">My Profile</h1>
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

    <!-- Profile Details -->
    <div class="mx-auto w-full max-w-4xl bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-5 sm:px-8 sm:py-6 border-b border-slate-200 bg-slate-50/50">
            <h3 class="font-bold text-slate-800 text-lg">Personal Information</h3>
            <p class="text-sm text-slate-500 mt-1">Your account details (Read-only)</p>
        </div>
        
        <div class="p-5 sm:p-8">
            <div class="flex flex-col md:flex-row gap-6 sm:gap-8 items-center md:items-start">
                
                <!-- Avatar -->
                <div class="shrink-0 flex flex-col items-center w-full md:w-auto">
                    <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff' }}" class="w-24 h-24 sm:w-32 sm:h-32 rounded-full border-4 border-indigo-50 shadow-md object-cover">
                    <span class="mt-4 px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-full uppercase tracking-wider">{{ $user->role ?? 'Employee' }}</span>
                </div>
                
                <!-- Info Grid -->
                <div class="flex-1 w-full grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                    
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Full Name</label>
                        <div class="break-words px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium cursor-not-allowed">
                            {{ $user->name }}
                        </div>
                    </div>
                    
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Email Address</label>
                        <div class="break-words px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium cursor-not-allowed">
                            {{ $user->email }}
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Phone Number</label>
                        <div class="break-words px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium cursor-not-allowed">
                            {{ $user->phone ?? '+92302...' }}
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Department</label>
                        <div class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium cursor-not-allowed flex items-center gap-2">
                            <i class="ph ph-buildings text-slate-400 text-lg"></i>
                            {{ $user->department->name ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Joined At</label>
                        <div class="px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-slate-700 font-medium cursor-not-allowed flex items-center gap-2">
                            <i class="ph ph-calendar text-slate-400 text-lg"></i>
                            {{ $user->created_at ? $user->created_at->format('F d, Y') : 'N/A' }}
                        </div>
                    </div>
                    
                </div>
            </div>
            
            <div class="mt-8 pt-6 border-t border-slate-100">
                <p class="text-xs text-slate-400 flex items-center gap-1.5">
                    <i class="ph-fill ph-info text-slate-300 text-sm"></i>
                    To update your profile information, please contact your administrator.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
