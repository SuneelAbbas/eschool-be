<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminInstituteController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminMessageController;
use App\Http\Controllers\Admin\AdminPlanController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminSettingController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard');
    }
    return app(App\Http\Controllers\Admin\AdminAuthController::class)->showLoginForm();
})->name('login');

Route::get('/login', fn() => redirect('/'));
Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/institutes', [AdminInstituteController::class, 'index'])->name('admin.institutes');
    Route::post('/institutes/{institute}/approve', [AdminInstituteController::class, 'approve'])->name('admin.institutes.approve');
    Route::post('/institutes/{institute}/reject', [AdminInstituteController::class, 'reject'])->name('admin.institutes.reject');
    Route::post('/institutes/{institute}/pending', [AdminInstituteController::class, 'pending'])->name('admin.institutes.pending');

    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users');

    Route::get('/messages', [AdminMessageController::class, 'index'])->name('admin.messages');
    Route::get('/messages/{contactMessage}', [AdminMessageController::class, 'show'])->name('admin.messages.show');
    Route::delete('/messages/{contactMessage}', [AdminMessageController::class, 'destroy'])->name('admin.messages.destroy');

    Route::get('/plans', [AdminPlanController::class, 'index'])->name('admin.plans');
    Route::get('/plans/create', [AdminPlanController::class, 'create'])->name('admin.plans.create');
    Route::post('/plans', [AdminPlanController::class, 'store'])->name('admin.plans.store');
    Route::get('/plans/{plan}/edit', [AdminPlanController::class, 'edit'])->name('admin.plans.edit');
    Route::put('/plans/{plan}', [AdminPlanController::class, 'update'])->name('admin.plans.update');
    Route::delete('/plans/{plan}', [AdminPlanController::class, 'destroy'])->name('admin.plans.destroy');

    Route::get('/roles', [AdminRoleController::class, 'index'])->name('admin.roles');
    Route::get('/roles/{role}', [AdminRoleController::class, 'show'])->name('admin.roles.show');

    Route::get('/settings', [AdminSettingController::class, 'index'])->name('admin.settings');
});

Route::get('/api-info', function () {
    return view('api-info');
});
