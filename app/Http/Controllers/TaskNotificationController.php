<?php

namespace App\Http\Controllers;

use App\Filament\Resources\TaskResource;
use App\Mail\TaskAssignedMail;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class TaskNotificationController extends Controller
{
    public function sendEmail(Task $task): RedirectResponse
    {
        $user = request()->user();

        abort_unless(
            $user && ($task->assigned_by === $user->id || $user->isAdmin() || $user->isManager()),
            403,
        );

        $assignee = $task->assignee;

        if (! $assignee || blank($assignee->email)) {
            return redirect(TaskResource::getUrl('index'))
                ->with('task_email_error', 'This employee does not have an email address.');
        }

        Mail::to($assignee)->send(new TaskAssignedMail($task));

        return redirect(TaskResource::getUrl('index'))
            ->with('task_email_sent', $assignee->name);
    }
}
