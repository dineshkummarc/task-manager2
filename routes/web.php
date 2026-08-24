<?php

use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\DailyReportExportController;
use App\Http\Controllers\EmployeeAuthController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\EmployeeTaskController;
use App\Http\Controllers\TaskNotificationController;
use App\Http\Controllers\UserTaskHistoryExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

Route::get('/employee/login', [EmployeeAuthController::class, 'showLoginForm'])->name('employee.login');
Route::post('/employee/login', [EmployeeAuthController::class, 'login'])->name('employee.login.submit');

Route::get('/employee', function () {
    return redirect('/employee/dashboard');
});

// This report is opened from the Filament admin panel. It must not use the
// employee-only middleware below, otherwise admins and managers receive 403.
Route::middleware('auth')->get('/admin/task-history/pdf', [UserTaskHistoryExportController::class, 'pdf'])
    ->name('user-task-history.pdf');
Route::middleware('auth')->get('/admin/daily-reports/pdf', [DailyReportExportController::class, 'pdf'])
    ->name('daily-reports.pdf');

Route::middleware(['auth', 'employee'])->group(function () {
    Route::get('/employee/dashboard', [EmployeeDashboardController::class, 'index'])->name('employee.dashboard');
    Route::get('/employee/tasks', [EmployeeDashboardController::class, 'allTasks'])->name('employee.tasks.index');
    Route::get('/employee/history', [EmployeeDashboardController::class, 'history'])->name('employee.history');
    Route::get('/employee/activities', [EmployeeDashboardController::class, 'activities'])->name('employee.activities');
    Route::get('/employee/profile', [EmployeeDashboardController::class, 'profile'])->name('employee.profile');
    Route::get('/employee/settings', [EmployeeDashboardController::class, 'settings'])->name('employee.settings');
    Route::get('/employee/notifications', [EmployeeDashboardController::class, 'notifications'])->name('employee.notifications');
    Route::post('/employee/notifications/read', [EmployeeDashboardController::class, 'markNotificationsRead'])->name('employee.notifications.read');
    Route::get('/employee/daily-report', [DailyReportController::class, 'create'])->name('employee.daily-report.create');
    Route::post('/employee/daily-report', [DailyReportController::class, 'store'])->name('employee.daily-report.store');

    Route::post('/employee/logout', [EmployeeAuthController::class, 'logout'])->name('employee.logout');

    // Task actions
    Route::post('/employee/tasks/create', [EmployeeTaskController::class, 'store'])->name('employee.tasks.store');
    Route::post('/employee/tasks/{task}/status', [EmployeeTaskController::class, 'updateStatus'])->name('employee.tasks.status');
});
Route::middleware('auth')->get('/tasks/{task}/notifications/email', [TaskNotificationController::class, 'sendEmail'])
    ->name('tasks.notifications.email');
