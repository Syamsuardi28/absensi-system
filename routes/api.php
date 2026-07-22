<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\QrController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\SchoolYearController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TeacherController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/auth/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::post('/attendance/scan', [AttendanceController::class, 'scan'])->middleware('throttle:30,1');
    Route::get('/attendance/history', [AttendanceController::class, 'history']);
    Route::post('/attendance/session', [AttendanceController::class, 'session']);

    Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    Route::get('/leave-requests/my', [LeaveRequestController::class, 'myRequests']);

    Route::middleware('role:super_admin|admin')->group(function () {
        Route::apiResource('school-years', SchoolYearController::class);
        Route::apiResource('classes', ClassController::class);
        Route::apiResource('subjects', SubjectController::class);
        Route::apiResource('schedules', ScheduleController::class);
        Route::apiResource('students', StudentController::class);
        Route::apiResource('teachers', TeacherController::class);

        Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
        Route::get('/attendance/report', [AttendanceController::class, 'report']);
        Route::post('/qr/regenerate/{userId}', [QrController::class, 'regenerate']);
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
    });

    Route::middleware('role:super_admin|admin|teacher')->group(function () {
        Route::get('/leave-requests/pending', [LeaveRequestController::class, 'pending']);
        Route::patch('/leave-requests/{id}/approve', [LeaveRequestController::class, 'approve']);
        Route::patch('/leave-requests/{id}/reject', [LeaveRequestController::class, 'reject']);
    });
});
