<?php
namespace App\Models;

use App\Mail\TaskAssignedMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;

class Task extends Model
{
    protected $guarded = [];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::created(function (self $task): void {
            $creator = $task->creator;
            $assignee = $task->assignee;

            // Send only once: when an administrator assigns a task to an employee.
            if (! $creator?->isAdmin() || ! $assignee?->isEmployee() || blank($assignee->email)) {
                return;
            }

            Mail::to($assignee)->send(new TaskAssignedMail($task));
        });
    }

    public function getEstimatedTimeAttribute(): string
    {
        $minutes = max(0, (int) ($this->estimated_minutes ?? 0));

        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }

    // Assigned Employee
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Assigning Manager
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // Task Logs
    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    // Time Variance Helper: Automatic Status Update
    public function updateVarianceStatus(): void
    {
        if ($this->estimated_minutes <= 0) {
            return;
        }

        $percentage = ($this->spent_minutes / $this->estimated_minutes) * 100;

        if ($percentage > 100) {
            $this->variance_status = 'overtime';
        } elseif ($percentage >= 80) {
            $this->variance_status = 'warning';
        } else {
            $this->variance_status = 'on_track';
        }

        $this->save();
    }
}
