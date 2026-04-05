<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CorrectionRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect('/attendance')
        : redirect('/login');
});

// Route::middleware(['auth:web', 'verified'])->group(function () {
//     Route::get('/home', fn () => view('attendance.home'));
// });
Route::middleware(['auth:web','verified'])->group(function () {
    Route::get('/attendance/', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::patch('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart']);
    Route::patch('/attendance/break-end', [AttendanceController::class, 'breakEnd']);
    Route::match(['get', 'post'],'/attendance/list', [AttendanceController::class, 'list']);
    Route::get('attendance/detail/{attendance_id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::post('attendance/detail/{attendance_id}', [AttendanceController::class, 'request'])->name('attendance.request');
    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'correction']);
});

// 管理者ログイン画面（GET）
Route::get('/admin/login', [AdminAuthController::class, 'index'])
    ->middleware('guest:admin')
    ->name('admin.login');

// 管理者ログイン処理（POST）
Route::post('/admin/login', [AdminAuthController::class, 'login'])
    ->middleware('guest:admin');


Route::middleware(['auth:admin','verified'])->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::match(['get', 'post'],'/admin/attendance/list', [AdminController::class, 'list'])->name('admin.list');
    Route::get('/admin/attendance/{attendance_id}', [AdminController::class, 'detail'])->name('admin.detail');
    Route::post('/admin/attendance/{attendance_id}', [AdminController::class, 'correction'])->name('admin.correction');
    Route::get('/admin/staff/list', [AdminController::class, 'staffList']);
    Route::match(['get', 'post'], '/admin/attendance/staff/{id}', [AdminController::class, 'staffDetail'])->name('admin.staff_detail');
    Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'correction'])->middleware('CorrectionRequestController');
});

// 管理者画面
Route::prefix('admin')->middleware('auth:admin')->group(function () {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('admin.dashboard');
});