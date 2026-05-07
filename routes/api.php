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
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\ExamTypeController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamResultController;
use App\Http\Controllers\ExamReportController;
use App\Http\Controllers\GradeSubjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\FeeTypeController;
use App\Http\Controllers\FeeCategoryController;
use App\Http\Controllers\FeeScheduleController;
use App\Http\Controllers\FeeSlipController;
use Illuminate\Support\Facades\Route;

Route::post('/institute-register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Institute settings (admin only)
Route::middleware('auth:sanctum', 'role:admin')->group(function () {
    Route::put('/institutes/current-academic-year', function (\Illuminate\Http\Request $request) {
        $request->validate(['current_academic_year' => 'required|string|size:9']);
        
        $user = $request->user();
        $institute = $user->institute;
        
        if (!$institute) {
            return response()->json(['success' => false, 'message' => 'Institute not found'], 404);
        }
        
        $institute->update(['current_academic_year' => $request->input('current_academic_year')]);
        
        return response()->json([
            'success' => true,
            'message' => 'Current academic year updated successfully',
            'data' => ['current_academic_year' => $institute->fresh()->current_academic_year]
        ]);
    });
});

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
        Route::get('/teachers/stats', [TeacherController::class, 'stats']);
        Route::post('/teachers', [TeacherController::class, 'store']);
        Route::get('/teachers/{id}', [TeacherController::class, 'show']);
        Route::put('/teachers/{id}', [TeacherController::class, 'update']);
        Route::delete('/teachers/{id}', [TeacherController::class, 'destroy']);
    });

    Route::middleware('role:admin,teacher,accountant')->group(function () {
        // V2 - Lightweight student list (recommended)
        Route::get('/v2/students', [StudentController::class, 'indexV2']);
        // V1 - Full student data (legacy)
        Route::get('/students', [StudentController::class, 'index']);
        Route::get('/students/stats', [StudentController::class, 'stats']);
        Route::post('/students', [StudentController::class, 'store']);
        Route::get('/students/generate-reg-number', [StudentController::class, 'generateRegNumber']);
        Route::get('/students/{id}', [StudentController::class, 'show']);
        Route::get('/students/{id}/edit', [StudentController::class, 'edit']);
        Route::put('/students/{id}', [StudentController::class, 'update']);
        Route::delete('/students/{id}', [StudentController::class, 'destroy']);
        Route::post('/students/{id}/enroll', [StudentController::class, 'enroll']);
        Route::post('/students/assign-fees', [StudentController::class, 'assignFeesToAllStudents']);
    });

    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::post('/attendance', [AttendanceController::class, 'store']);
        Route::get('/attendance/report', [AttendanceController::class, 'report']);
        Route::get('/attendance/section', [AttendanceController::class, 'sectionAttendance']);
        Route::get('/attendance/section/stats', [AttendanceController::class, 'stats']);
        Route::get('/students/{studentId}/attendance-summary', [AttendanceController::class, 'studentAttendanceSummary']);
        Route::get('/attendance/{id}', [AttendanceController::class, 'show']);
        Route::put('/attendance/{id}', [AttendanceController::class, 'update']);
        Route::delete('/attendance/{id}', [AttendanceController::class, 'destroy']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/subjects', [SubjectController::class, 'index']);
        Route::get('/subjects/stats', [SubjectController::class, 'stats']);
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

    Route::middleware('role:admin')->group(function () {
        // Separate endpoints for section head and subject assignment
        Route::post('/teacher-sections/section-head', [TeacherSectionController::class, 'assignSectionHead']);
        Route::post('/teacher-sections/subject', [TeacherSectionController::class, 'assignSubject']);

        // Grade Subjects
        Route::get('/grade-subjects', [GradeSubjectController::class, 'index']);
        Route::post('/grade-subjects', [GradeSubjectController::class, 'store']);
        Route::delete('/grade-subjects/{id}', [GradeSubjectController::class, 'destroy']);
        Route::post('/grade-subjects/clear-grade', [GradeSubjectController::class, 'clearGrade']);
        Route::get('/grades/{gradeId}/subjects', [GradeSubjectController::class, 'getSubjectsForGrade']);
    });

    Route::middleware('role:admin,accountant')->group(function () {
        // TODO: Fee management routes will be re-implemented
        
        Route::get('/exam-types', [ExamTypeController::class, 'index']);
        Route::post('/exam-types', [ExamTypeController::class, 'store']);
        Route::get('/exam-types/{id}', [ExamTypeController::class, 'show']);
        Route::put('/exam-types/{id}', [ExamTypeController::class, 'update']);
Route::delete('/exam-types/{id}', [ExamTypeController::class, 'destroy']);

        Route::get('/exams', [ExamController::class, 'index']);
        Route::post('/exams', [ExamController::class, 'store']);
        Route::get('/exams/{id}', [ExamController::class, 'show']);
        Route::put('/exams/{id}', [ExamController::class, 'update']);
        Route::delete('/exams/{id}', [ExamController::class, 'destroy']);
        Route::get('/exams/{id}/students', [ExamController::class, 'students']);
        Route::post('/exams/{id}/subjects', [ExamController::class, 'addSubjects']);

        // Grade Subjects
        Route::post('/exams', [ExamController::class, 'store']);
        Route::get('/exams/{id}', [ExamController::class, 'show']);
        Route::put('/exams/{id}', [ExamController::class, 'update']);
        Route::delete('/exams/{id}', [ExamController::class, 'destroy']);
        Route::get('/exams/{id}/students', [ExamController::class, 'students']);
        Route::post('/exams/{id}/subjects', [ExamController::class, 'addSubjects']);

        Route::get('/exam-results', [ExamResultController::class, 'index']);
        Route::post('/exam-results', [ExamResultController::class, 'store']);
        Route::post('/exam-results/bulk', [ExamResultController::class, 'bulkStore']);
        Route::get('/exam-results/{id}', [ExamResultController::class, 'show']);
        Route::put('/exam-results/{id}', [ExamResultController::class, 'update']);
        Route::delete('/exam-results/{id}', [ExamResultController::class, 'destroy']);
        Route::get('/exams/{id}/results', [ExamResultController::class, 'byExam']);
        Route::get('/students/{studentId}/exam-results', [ExamResultController::class, 'byStudent']);

        Route::get('/report-cards', [ReportCardController::class, 'index']);
        Route::get('/report-cards/{id}', [ReportCardController::class, 'show']);
        Route::post('/report-cards/generate', [ReportCardController::class, 'generate']);
        Route::get('/report-cards/student/{studentId}', [ReportCardController::class, 'studentHistory']);
        Route::delete('/report-cards/{id}', [ReportCardController::class, 'destroy']);

        Route::get('/exam-reports/summary', [ExamReportController::class, 'summary']);
        Route::get('/exam-reports/grade-analysis', [ExamReportController::class, 'gradeAnalysis']);
        Route::get('/exam-reports/subject-analysis', [ExamReportController::class, 'subjectAnalysis']);
        Route::get('/exam-reports/student-comparison', [ExamReportController::class, 'studentComparison']);
    });

    // User Management Routes
    Route::middleware('permission:users.view')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{user}', [UserController::class, 'show']);
    });

    Route::middleware('permission:users.create')->group(function () {
        Route::post('/users', [UserController::class, 'store']);
    });

    Route::middleware('permission:users.update')->group(function () {
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::patch('/users/{user}', [UserController::class, 'update']);
    });

    Route::middleware('permission:users.delete')->group(function () {
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
    });

    Route::middleware('permission:users.assign_role')->group(function () {
        Route::post('/users/{user}/roles', [UserController::class, 'assignRoles']);
    });

    Route::middleware('permission:users.toggle_status')->group(function () {
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    });

    Route::middleware('permission:users.update')->group(function () {
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
    });

    Route::get('/me/permissions', [UserController::class, 'myPermissions']);

    // Fee Management Routes (Admin, Accountant)
    Route::middleware('role:admin,accountant')->group(function () {
        // Fee Types (Fee Head Definitions)
        Route::get('/fee-types', [FeeTypeController::class, 'index']);
        Route::post('/fee-types', [FeeTypeController::class, 'store']);
        Route::delete('/fee-types/bulk-delete', [FeeTypeController::class, 'bulkDestroy']);
        Route::get('/fee-types/{id}', [FeeTypeController::class, 'show']);
        Route::put('/fee-types/{id}', [FeeTypeController::class, 'update']);
        Route::delete('/fee-types/{id}', [FeeTypeController::class, 'destroy']);

        // Fee Categories (New, Old, RTE, etc.)
        Route::get('/fee-categories', [FeeCategoryController::class, 'index']);
        Route::post('/fee-categories', [FeeCategoryController::class, 'store']);
        Route::delete('/fee-categories/bulk-delete', [FeeCategoryController::class, 'bulkDestroy']);
        Route::get('/fee-categories/{id}', [FeeCategoryController::class, 'show']);
        Route::put('/fee-categories/{id}', [FeeCategoryController::class, 'update']);
        Route::delete('/fee-categories/{id}', [FeeCategoryController::class, 'destroy']);
        Route::delete('/fee-categories/bulk-delete', [FeeCategoryController::class, 'bulkDestroy']);

        // Fee Schedules (Mapping fees to grades with frequency)
        Route::get('/fee-schedules', [FeeScheduleController::class, 'index']);
        Route::post('/fee-schedules', [FeeScheduleController::class, 'store']);
        Route::delete('/fee-schedules/bulk-delete', [FeeScheduleController::class, 'bulkDestroy']);
        Route::post('/fee-schedules/generate-student-fees', [FeeScheduleController::class, 'generateStudentFees']);
        
        // New: Bulk save all fees for a grade at once
        Route::post('/fee-structure/grade/{gradeId}/save', [FeeScheduleController::class, 'saveGradeFees']);
        Route::get('/fee-structure/grade/{gradeId}', [FeeScheduleController::class, 'getGradeFees']);
        Route::delete('/fee-structure/grade/{gradeId}', [FeeScheduleController::class, 'deleteGradeFees']);
        
        // Get all grades fee structure (with pagination & filters)
        Route::get('/fee-structure/all', [FeeScheduleController::class, 'getAllGradesFees']);
        
        Route::get('/fee-schedules/{id}', [FeeScheduleController::class, 'show']);
        Route::put('/fee-schedules/{id}', [FeeScheduleController::class, 'update']);
        Route::delete('/fee-schedules/{id}', [FeeScheduleController::class, 'destroy']);

        // Fee Slips (Generate with transaction_id for bank payments)
        Route::post('/fee-slips/generate', [FeeSlipController::class, 'generatebulk']);
        Route::post('/fee-slips/generate/{studentId}', [FeeSlipController::class, 'generateSingle']);
        
        // View generated fee slips
        Route::get('/fee-slips', [FeeSlipController::class, 'index']);
        Route::delete('/fee-slips/bulk-delete', [FeeSlipController::class, 'bulkDelete']);
        Route::delete('/fee-slips/delete-all', [FeeSlipController::class, 'deleteAll']);
        Route::get('/fee-slips/{id}', [FeeSlipController::class, 'view']);
        Route::delete('/fee-slips/{id}', [FeeSlipController::class, 'delete']);
    });

    // Role & Permission Routes (Admin only)
    Route::middleware('role:admin,super_admin')->group(function () {
        Route::get('/roles', [RoleController::class, 'index']);
        Route::post('/roles', [RoleController::class, 'store']);
        Route::get('/roles/{role}', [RoleController::class, 'show']);
        Route::put('/roles/{role}', [RoleController::class, 'update']);
        Route::patch('/roles/{role}', [RoleController::class, 'update']);
        Route::delete('/roles/{role}', [RoleController::class, 'destroy']);

        Route::get('/permissions', [PermissionController::class, 'index']);
        Route::get('/permissions/groups', [PermissionController::class, 'groups']);
    });
});
