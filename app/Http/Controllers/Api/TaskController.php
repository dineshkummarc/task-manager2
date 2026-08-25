<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Models\UserActivity;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,in_progress,blockage,completed'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $tasks = Task::query()
            ->where('assigned_to', $request->user()->id)
            ->with(['project', 'department'])
            ->when($validated['status'] ?? null, function ($query, string $status) {
                $status === 'blockage'
                    ? $query->where('is_blocked', true)
                    : $query->where('status', $status)->where('is_blocked', false);
            })
            ->when($validated['project_id'] ?? null, fn ($query, int $projectId) => $query->where('project_id', $projectId))
            ->orderByRaw('due_date IS NULL, due_date')
            ->latest('id')
            ->paginate($validated['per_page'] ?? 15);

        return TaskResource::collection($tasks);
    }

    public function show(Request $request, Task $task): TaskResource
    {
        abort_unless((int) $task->assigned_to === (int) $request->user()->id, 403);

        return new TaskResource($task->load(['project', 'department']));
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): TaskResource
    {
        $validated = $request->validated();
        $isBlocked = $validated['status'] === 'blockage';

        $task->update([
            'status' => $validated['status'],
            'is_blocked' => $isBlocked,
            'block_reason' => $isBlocked ? $validated['block_reason'] : null,
            'completion_note' => $validated['status'] === 'completed' ? ($validated['completion_note'] ?? null) : null,
        ]);

        UserActivity::create([
            'user_id' => $request->user()->id,
            'action' => 'updated status',
            'description' => 'Updated task "'.$task->title.'" status to '.$validated['status'],
        ]);

        return new TaskResource($task->load(['project', 'department']));
    }
}
