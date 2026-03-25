<?php

use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\Manager\AttendanceApprovalController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->middleware('role:Admin|Manager|Employee')
        ->name('attendance.index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])
        ->middleware('role:Admin|Manager|Employee')
        ->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])
        ->middleware('role:Admin|Manager|Employee')
        ->name('attendance.check-out');

    Route::get('/leaves', [LeaveController::class, 'index'])
        ->middleware('role:Admin|Manager|Employee')
        ->name('leaves.index');
    Route::post('/leaves', [LeaveController::class, 'store'])
        ->middleware('role:Admin|Manager|Employee')
        ->name('leaves.store');
    Route::patch('/leaves/{leaveRequest}/status', [LeaveController::class, 'updateStatus'])
        ->middleware('role:Admin|Manager')
        ->name('leaves.status');

    Route::prefix('manager')->name('manager.')->middleware('role:Manager|Admin')->group(function () {
        Route::get('/attendance-approvals', [AttendanceApprovalController::class, 'index'])->name('attendance-approvals.index');
        Route::patch('/attendance-approvals/{attendance}/approve', [AttendanceApprovalController::class, 'approve'])->name('attendance-approvals.approve');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:Admin')->group(function () {
        Route::resource('/employees', EmployeeController::class)
            ->parameters(['employees' => 'employee'])
            ->except(['show']);
        Route::get('/attendance-reports', [AttendanceReportController::class, 'index'])->name('attendance-reports.index');
        Route::patch('/attendance-reports/{attendance}/override', [AttendanceReportController::class, 'override'])->name('attendance-reports.override');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
