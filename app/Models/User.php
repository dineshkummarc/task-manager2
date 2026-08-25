<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens; // 1. Yahan Sanctum trait import karein

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable; // 2. Yahan 'HasApiTokens' add kar dein

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'task_notifications_read_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        // Filament admin panel is reserved for administrators and managers.
        return $this->isAdmin() || $this->isManager();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager') || $this->role === 'manager';
    }

    public function isEmployee(): bool
    {
        return $this->hasRole('employee') || $this->role === 'employee';
    }

    public function isEmployeeOnly(): bool
    {
        return $this->isEmployee() && ! $this->isAdmin() && ! $this->isManager();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_by');
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TimeLog::class);
    }

    public function dailyPerformances(): HasMany
    {
        return $this->hasMany(DailyPerformance::class);
    }

    public function dailyReportEntries(): HasMany
    {
        return $this->hasMany(DailyReportEntry::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(UserActivity::class);
    }

    public function jobResponsibility()
    {
        return $this->belongsTo(JobResponsibility::class);
    }

    /**
     * Accurate Performance Percentage Calculation:
     * - New user jiske paas abhi koi task nahi hai ya completed task nahi hai (lekin overdue bhi nahi hai), uski starting performance 100% hogi.
     * - Overdue penalty calculate hoti hai.
     */
    public function getPerformancePercentage($startDate = null, $endDate = null): float
    {
        $baseQuery = $this->assignedTasks();
        if ($startDate) {
            $baseQuery->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $baseQuery->where('created_at', '<=', $endDate);
        }

        $totalAssigned = (clone $baseQuery)->count();

        $overdueCount = (clone $baseQuery)
            ->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();

        // Agar new user hai aur koi task assign nahi hua, toh default 100%
        if ($totalAssigned === 0) {
            return 100.0;
        }

        $completedTasks = (clone $baseQuery)
            ->where('status', 'completed')
            ->selectRaw('SUM(COALESCE(estimated_minutes, 0)) as total_est, SUM(GREATEST(TIMESTAMPDIFF(MINUTE, created_at, updated_at), 1)) as total_actual, count(*) as completed_count')
            ->first();

        $completedCount = $completedTasks ? (int) $completedTasks->completed_count : 0;

        // Agar koi task complete nahi hua lekin overdue hain
        if ($completedCount === 0) {
            if ($overdueCount > 0) {
                return max(0.0, 100.0 - ($overdueCount * 25));
            }

            // Agar task in-progress ya pending hai aur overdue nahi hai, toh starting 100%
            return 100.0;
        }

        $est = (float) ($completedTasks->total_est ?? 0);
        $actual = (float) ($completedTasks->total_actual ?? 1);

        $timeRatio = $est > 0 ? ($est / $actual) * 100 : 100;
        $completionRate = ($completedCount / $totalAssigned);

        $score = $timeRatio * $completionRate;

        // Penalty for overdue tasks
        $penalty = $overdueCount * 15;
        $finalScore = max(0, min(100, $score - $penalty));

        return round($finalScore, 1);
    }
}