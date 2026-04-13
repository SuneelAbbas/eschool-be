<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ClassSectionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherSectionController;
use App\Http\Controllers\FeeTypeController;
use App\Http\Controllers\GradeFeeController;
use App\Http\Controllers\StudentFeeController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\StudentDiscountController;
use App\Http\Controllers\FeePaymentController;
use Illuminate\Support\Facades\Route;

Route::post('/institute-register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('role:admin,teacher,accountant')->group(function () {
        Route::get('/grades', [GradeController::class, 'index']);
        Route::post('/grades', [GradeController::class, 'store']);
        Route::get('/grades/{id}', [GradeController::class, 'show']);
        Route::put('/grades/{id}', [GradeController::class, 'update']);
        Route::delete('/grades/{id}', [GradeController::class, 'destroy']);
    });

    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/sections', [SectionController::class, 'index']);
        Route::post('/sections', [SectionController::class, 'store']);
        Route::get('/sections/{id}', [SectionController::class, 'show']);
        Route::put('/sections/{id}', [SectionController::class, 'update']);
        Route::delete('/sections/{id}', [SectionController::class, 'destroy']);
    });

    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/class-sections', [ClassSectionController::class, 'index']);
        Route::post('/class-sections', [ClassSectionController::class, 'store']);
        Route::get('/class-sections/{id}', [ClassSectionController::class, 'show']);
        Route::put('/class-sections/{id}', [ClassSectionController::class, 'update']);
        Route::delete('/class-sections/{id}', [ClassSectionController::class, 'destroy']);
    });

    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/teachers', [TeacherController::class, 'index']);
        Route::post('/teachers', [TeacherController::class, 'store']);
        Route::get('/teachers/{id}', [TeacherController::class, 'show']);
        Route::put('/teachers/{id}', [TeacherController::class, 'update']);
        Route::delete('/teachers/{id}', [TeacherController::class, 'destroy']);
    });

    Route::middleware('role:admin,teacher,accountant')->group(function () {
        Route::get('/students', [StudentController::class, 'index']);
        Route::post('/students', [StudentController::class, 'store']);
        Route::get('/students/{id}', [StudentController::class, 'show']);
        Route::put('/students/{id}', [StudentController::class, 'update']);
        Route::delete('/students/{id}', [StudentController::class, 'destroy']);
    });

    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::post('/attendance', [AttendanceController::class, 'store']);
        Route::get('/attendance/report', [AttendanceController::class, 'report']);
        Route::get('/attendance/section', [AttendanceController::class, 'sectionAttendance']);
        Route::get('/attendance/{id}', [AttendanceController::class, 'show']);
        Route::put('/attendance/{id}', [AttendanceController::class, 'update']);
        Route::delete('/attendance/{id}', [AttendanceController::class, 'destroy']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/subjects', [SubjectController::class, 'index']);
        Route::post('/subjects', [SubjectController::class, 'store']);
        Route::get('/subjects/{id}', [SubjectController::class, 'show']);
        Route::put('/subjects/{id}', [SubjectController::class, 'update']);
        Route::delete('/subjects/{id}', [SubjectController::class, 'destroy']);
    });

    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/teacher-sections', [TeacherSectionController::class, 'index']);
        Route::post('/teacher-sections', [TeacherSectionController::class, 'store']);
        Route::get('/teacher-sections/{id}', [TeacherSectionController::class, 'show']);
        Route::put('/teacher-sections/{id}', [TeacherSectionController::class, 'update']);
        Route::delete('/teacher-sections/{id}', [TeacherSectionController::class, 'destroy']);
    });

    Route::middleware('role:admin,accountant')->group(function () {
        Route::get('/fee-types', [FeeTypeController::class, 'index']);
        Route::post('/fee-types', [FeeTypeController::class, 'store']);
        Route::get('/fee-types/{id}', [FeeTypeController::class, 'show']);
        Route::put('/fee-types/{id}', [FeeTypeController::class, 'update']);
        Route::delete('/fee-types/{id}', [FeeTypeController::class, 'destroy']);

        Route::get('/grade-fees', [GradeFeeController::class, 'index']);
        Route::post('/grade-fees', [GradeFeeController::class, 'store']);
        Route::get('/grade-fees/{id}', [GradeFeeController::class, 'show']);
        Route::put('/grade-fees/{id}', [GradeFeeController::class, 'update']);
        Route::delete('/grade-fees/{id}', [GradeFeeController::class, 'destroy']);

        Route::get('/student-fees', [StudentFeeController::class, 'index']);
        Route::post('/student-fees', [StudentFeeController::class, 'store']);
        Route::get('/student-fees/{id}', [StudentFeeController::class, 'show']);
        Route::put('/student-fees/{id}', [StudentFeeController::class, 'update']);
        Route::delete('/student-fees/{id}', [StudentFeeController::class, 'destroy']);

        Route::get('/discounts', [DiscountController::class, 'index']);
        Route::post('/discounts', [DiscountController::class, 'store']);
        Route::get('/discounts/{id}', [DiscountController::class, 'show']);
        Route::put('/discounts/{id}', [DiscountController::class, 'update']);
        Route::delete('/discounts/{id}', [DiscountController::class, 'destroy']);

        Route::get('/student-discounts', [StudentDiscountController::class, 'index']);
        Route::post('/student-discounts', [StudentDiscountController::class, 'store']);
        Route::get('/student-discounts/{id}', [StudentDiscountController::class, 'show']);
        Route::put('/student-discounts/{id}', [StudentDiscountController::class, 'update']);
        Route::delete('/student-discounts/{id}', [StudentDiscountController::class, 'destroy']);

        Route::get('/fee-payments', [FeePaymentController::class, 'index']);
        Route::post('/fee-payments', [FeePaymentController::class, 'store']);
        Route::get('/fee-payments/defaulters', [FeePaymentController::class, 'defaulters']);
        Route::get('/fee-payments/{id}', [FeePaymentController::class, 'show']);
        Route::delete('/fee-payments/{id}', [FeePaymentController::class, 'destroy']);
    });
});
