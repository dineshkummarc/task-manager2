<?php

use App\Http\Controllers\Api\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\Api\Admin\EmployeeController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\WorkspaceFeedController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DailyReportController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

// Public Route (Login)
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Token required)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/projects', [ProjectController::class, 'index']);

    Route::get('/tasks', [TaskController::class, 'index']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
});

Route::prefix('employee')->middleware(['auth:sanctum', 'employee'])->group(function () {
    Route::apiResource('daily-reports', DailyReportController::class)
        ->parameters(['daily-reports' => 'dailyReport']);
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('employees', EmployeeController::class);
    Route::apiResource('departments', AdminDepartmentController::class);
    Route::get('reports/daily', [ReportController::class, 'daily']);
    Route::get('reports/performance', [ReportController::class, 'performance']);
    Route::get('workspace-feed', [WorkspaceFeedController::class, 'index']);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('permissions', PermissionController::class)->only(['index', 'store', 'update', 'destroy']);
});
