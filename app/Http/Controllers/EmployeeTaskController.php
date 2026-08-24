<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeTaskController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_minutes' => 'nullable|integer|min:0',
            'project_id' => 'required|exists:projects,id',
        ]);

        $user = Auth::user();

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'estimated_minutes' => $request->estimated_minutes ?? 0,
            'user_id' => $user->id,
            'assigned_to' => $user->id,
            'assigned_by' => $user->id,
            'project_id' => $request->integer('project_id'),
            'department_id' => $user->department_id,
            'job_responsibility' => $user->job_responsibility,
            'status' => 'pending',
            'due_date' => null,
        ]);

        UserActivity::create([
            'user_id' => $user->id,
            'action' => 'created task',
            'description' => 'Created a new task: '.$task->title,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Task created successfully.']);
        }

        return back()->with('success', 'Task created successfully.');
    }

    public function updateStatus(Request $request, Task $task)
    {
        $user = Auth::user();

        // Ensure task belongs to user
        if ($task->assigned_to !== $user->id && $task->user_id !== $user->id) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,in_progress,blockage,completed',
            'block_reason' => 'nullable|string',
            'completion_note' => 'nullable|string|max:5000',
        ]);

        $newStatus = $request->status;
        $isBlocked = $newStatus === 'blockage';

        $task->update([
            'status' => $newStatus,
            'is_blocked' => $isBlocked,
            'block_reason' => $isBlocked ? $request->block_reason : null,
            'completion_note' => $newStatus === 'completed' ? $request->completion_note : null,
        ]);

        UserActivity::create([
            'user_id' => $user->id,
            'action' => 'updated status',
            'description' => 'Updated task "'.$task->title.'" status to '.$newStatus
                .($isBlocked && $request->block_reason ? ' (Reason: '.$request->block_reason.')' : '')
                .($newStatus === 'completed' && $request->completion_note ? ' (Completion note added)' : ''),
        ]);

        return back()->with('success', 'Task status updated to '.ucfirst($newStatus).'.');
    }
}
