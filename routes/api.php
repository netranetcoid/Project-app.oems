<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\EmployeeHomeController;
use App\Http\Controllers\Api\EmployeeRequestController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\MobileReleaseController;
use App\Http\Controllers\Api\AppBillAttendanceController;

// Route untuk akses publik (tidak perlu login)
// Endpoint versi; alias /login dipertahankan untuk klien lama.
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::get('/mobile/release/check', [MobileReleaseController::class, 'check'])->middleware('throttle:60,1');

// Kontrak eksternal AppBill. Akses tidak memakai session/Sanctum pegawai;
// setiap request wajib Bearer token, company code, request ID, dan HMAC.
Route::prefix('integrations/appbill')
    ->middleware(['appbill.integration', 'throttle:120,1'])
    ->group(function (): void {
        // Machine-readable contract; protected by the same AppBill HMAC.
        Route::get('/attendance-contract', [AppBillAttendanceController::class, 'attendanceContract']);
        Route::get('/employees', [AppBillAttendanceController::class, 'employees']);
        Route::get('/shifts', [AppBillAttendanceController::class, 'shifts']);
        Route::get('/attendance', [AppBillAttendanceController::class, 'attendance']);
        Route::get('/attendance/{sourceRecordId}', [AppBillAttendanceController::class, 'showAttendance']);
        Route::post('/attendance', [AppBillAttendanceController::class, 'storeAttendance']);
        Route::put('/attendance/{sourceRecordId}', [AppBillAttendanceController::class, 'updateAttendance']);
        Route::delete('/attendance/{sourceRecordId}', [AppBillAttendanceController::class, 'destroyAttendance']);
    });

// Route yang butuh login (semuanya masuk ke middleware yang sama)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('/attendance/today', [AttendanceController::class, 'today']);
    Route::get('/employee/me/home', EmployeeHomeController::class);
    Route::get('/requests', [EmployeeRequestController::class, 'index']);
    Route::post('/requests', [EmployeeRequestController::class, 'store']);
    Route::get('/payrolls', [PayrollController::class, 'index']);
    Route::get('/payrolls/{slip}', [PayrollController::class, 'show']);
    Route::get('/payrolls/{slip}/payslip', [PayrollController::class, 'payslip']);
    Route::get('/mobile/config', [MobileReleaseController::class, 'config']);
});
