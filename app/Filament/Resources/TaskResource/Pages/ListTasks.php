<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    protected static string $resource = TaskResource::class;

    public function mount(): void
    {
        parent::mount();

        if ($employeeName = session()->pull('task_email_sent')) {
            Notification::make()
                ->success()
                ->title('Email sent successfully')
                ->body('The task notification was emailed to '.$employeeName.'.')
                ->send();
        }

        if ($message = session()->pull('task_email_error')) {
            Notification::make()
                ->danger()
                ->title('Email could not be sent')
                ->body($message)
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
