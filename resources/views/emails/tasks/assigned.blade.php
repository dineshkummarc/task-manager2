<x-mail::message>
# Hello {{ $task->assignee->name ?? 'Employee' }},

You have been assigned a new task. Please review the details below:

**Task Title:** {{ $task->title }}  
**Description:** {{ $task->description ?? 'No description provided.' }}  

<x-mail::button :url="url('/employee/login')">
Log in to View Dashboard
</x-mail::button>

Best regards,  
{{ config('app.name') }}
</x-mail::message>