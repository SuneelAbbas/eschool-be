<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/institute-register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/me', [AuthController::class, 'me']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/grades', [\App\Http\Controllers\GradeController::class, 'index']);
Route::post('/grades', [\App\Http\Controllers\GradeController::class, 'store']);
Route::get('/grades/{id}', [\App\Http\Controllers\GradeController::class, 'show']);
Route::put('/update-grade/{id}', [\App\Http\Controllers\GradeController::class, 'update']);
// Route::post('/update-grade/{id}', [\App\Http\Controllers\GradeController::class, 'update']);
Route::delete('/delete-grade/{id}', [\App\Http\Controllers\GradeController::class, 'destroy']);

Route::get('/class-sections', [\App\Http\Controllers\ClassSectionController::class, 'index']);
Route::post('/class-sections', [\App\Http\Controllers\ClassSectionController::class, 'store']);
Route::get('/class-sections/{id}', [\App\Http\Controllers\ClassSectionController::class, 'show']);
Route::post('/update-class-sections/{id}', [\App\Http\Controllers\ClassSectionController::class, 'update']);
Route::delete('/class-sections/{id}', [\App\Http\Controllers\ClassSectionController::class, 'destroy']);
// New Section CRUD routes
Route::get('/sections', [\App\Http\Controllers\SectionController::class, 'index']);
Route::post('/sections', [\App\Http\Controllers\SectionController::class, 'store']);
Route::get('/sections/{id}', [\App\Http\Controllers\SectionController::class, 'show']);
Route::post('/update-sections/{id}', [\App\Http\Controllers\SectionController::class, 'update']);
Route::delete('/sections/{id}', [\App\Http\Controllers\SectionController::class, 'destroy']);

Route::get('/students', [\App\Http\Controllers\StudentController::class, 'index']);
Route::post('/students', [\App\Http\Controllers\StudentController::class, 'store']);
Route::get('/students/{id}', [\App\Http\Controllers\StudentController::class, 'show']);
Route::put('/students/{id}', [\App\Http\Controllers\StudentController::class, 'update']);
Route::delete('/students/{id}', [\App\Http\Controllers\StudentController::class, 'destroy']);

Route::get('/teachers', [\App\Http\Controllers\TeacherController::class, 'index']);
Route::post('/teachers', [\App\Http\Controllers\TeacherController::class, 'store']);
Route::get('/teachers/{id}', [\App\Http\Controllers\TeacherController::class, 'show']);
Route::put('/teachers/{id}', [\App\Http\Controllers\TeacherController::class, 'update']);
Route::delete('/teachers/{id}', [\App\Http\Controllers\TeacherController::class, 'destroy']);
