<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\UserActivity;
use App\Models\Project;

class EmployeeDashboardController extends Controller
{
    public function notifications(Request $request)
    {
        $user = $request->user();
        $tasks = $user->assignedTasks()->with('creator')->latest()->take(5)->get();
        $unreadQuery = $user->assignedTasks();

        if ($user->task_notifications_read_at) {
            $unreadQuery->where('created_at', '>', $user->task_notifications_read_at);
        }

        return response()->json([
            'unreadCount' => $unreadQuery->count(),
            'tasks' => $tasks->map(fn (Task $task) => [
                'title' => $task->title,
                'assignedBy' => $task->creator?->name ?? 'Self',
                'createdAt' => $task->created_at?->diffForHumans() ?? 'Just now',
            ])->values(),
        ]);
    }

    public function markNotificationsRead(Request $request)
    {
        $request->user()->forceFill([
            'task_notifications_read_at' => now(),
        ])->save();

        return response()->json(['ok' => true]);
    }

    public function index()
    {
        $user = Auth::user();

        // Task Statistics
        $totalTasks = Task::where(function($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('user_id', $user->id);
            })->count();
            
        $inProgressTasks = Task::where(function($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('user_id', $user->id);
            })->where('status', 'in_progress')->count();
            
        $completedTasks = Task::where(function($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('user_id', $user->id);
            })->where('status', 'completed')->count();
            
        $overdueTasks = Task::where(function($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('user_id', $user->id);
            })
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();

        $pendingTasks = Task::where(function($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('user_id', $user->id);
            })->where('status', 'pending')->count();

        // My Tasks: status filtering is handled instantly in the dashboard UI.
        $tasksQuery = Task::where(function($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('user_id', $user->id);
            })
            ->with('creator');
            
        $tasks = $tasksQuery->latest('created_at')
            ->paginate(6)
            ->withQueryString();

        // Recent Activity (Real data from user_activities)
        $recentActivity = \App\Models\UserActivity::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        // Pass active timer
        $activeTimer = \App\Models\TimeLog::where('user_id', $user->id)
            ->whereNull('end_time')
            ->first();

        $projects = Project::query()
            ->orderByRaw("name = 'BPOVO' DESC")
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('employee.dashboard', compact(
            'user', 
            'totalTasks', 
            'pendingTasks',
            'inProgressTasks', 
            'completedTasks', 
            'overdueTasks', 
            'tasks', 
            'recentActivity',
            'activeTimer',
            'projects'
        ));
    }
    public function allTasks()
    {
        $user = Auth::user();
        $tasks = Task::where(function($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('user_id', $user->id);
            })
            ->where('status', '!=', 'completed')
            ->orderByRaw("CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC")
            ->paginate(15);
            
        return view('employee.tasks', compact('user', 'tasks'));
    }

    public function history()
    {
        $user = Auth::user();
        $tasks = Task::where(function($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhere('user_id', $user->id);
            })
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
            
        return view('employee.history', compact('user', 'tasks'));
    }

    public function activities()
    {
        $user = Auth::user();
        $activities = UserActivity::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return view('employee.activities', compact('user', 'activities'));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('employee.profile', compact('user'));
    }

    public function settings()
    {
        $user = Auth::user();
        return view('employee.settings', compact('user'));
    }
}
