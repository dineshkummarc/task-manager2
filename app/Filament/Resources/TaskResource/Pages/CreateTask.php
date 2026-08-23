<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTask extends CreateRecord
{
    protected static string $resource = TaskResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // The logged-in admin or manager is always the task assigner.
        $data['assigned_by'] = Auth::id();

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var Task $task */
        $task = $this->getRecord();
        $assignee = $task->assignee;
        $phone = preg_replace('/\D+/', '', (string) $assignee?->phone);
        $message = "\u{1F4CB} *New Task Assigned!*\n\n" .
           "\u{1F4CC} *Title:* " . $task->title . "\n\n" .
           "\u{1F4DD} *Description:* " . strip_tags($task->description ?? 'N/A') . "\n\n" .
           "\u{1F464} *Assigned by:* *" . auth()->user()->name . "*";

        session()->flash('task_notification_choice', [
            'employeeName' => $assignee?->name ?? 'the employee',
            'emailUrl' => route('tasks.notifications.email', $task),
            'whatsAppUrl' => filled($phone)
                ? 'https://wa.me/'.$phone.'?text='.rawurlencode($message)
                : null,
        ]);
    }
}
