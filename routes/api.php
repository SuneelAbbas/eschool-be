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
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\ExamTypeController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamResultController;
use App\Http\Controllers\ReportCardController;
use App\Http\Controllers\ExamReportController;
use App\Http\Controllers\GradeSubjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
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
        Route::get('/students/stats', [StudentController::class, 'stats']);
        Route::post('/students', [StudentController::class, 'store']);
        Route::get('/students/generate-reg-number', [StudentController::class, 'generateRegNumber']);
        Route::get('/students/{id}', [StudentController::class, 'show']);
        Route::get('/students/{id}/dashboard-data', [StudentController::class, 'dashboardData']);
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
        Route::get('/students/{studentId}/attendance-summary', [AttendanceController::class, 'studentAttendanceSummary']);
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
        Route::post('/fee-types/batch', [FeeTypeController::class, 'destroyBatch']);
        Route::get('/fee-types/{id}', [FeeTypeController::class, 'show']);
        Route::put('/fee-types/{id}', [FeeTypeController::class, 'update']);
        Route::delete('/fee-types/{id}', [FeeTypeController::class, 'destroy']);

Route::get('/grade-fees', [GradeFeeController::class, 'index']);
        Route::post('/grade-fees', [GradeFeeController::class, 'store']);
        Route::post('/grade-fees/batch', [GradeFeeController::class, 'storeBatch']);
        Route::put('/grade-fees/batch', [GradeFeeController::class, 'updateBatch']);
        Route::post('/grade-fees/batch-delete', [GradeFeeController::class, 'destroyBatch']);
        Route::post('/grades/{gradeId}/fees/assign-to-students', [GradeFeeController::class, 'assignToStudents']);
        Route::get('/grades/{gradeId}/fees/students-without-fee', [GradeFeeController::class, 'getStudentsWithoutFee']);
        Route::get('/grade-fees/{id}', [GradeFeeController::class, 'show']);
        Route::put('/grade-fees/{id}', [GradeFeeController::class, 'update']);
        Route::delete('/grade-fees/{id}', [GradeFeeController::class, 'destroy']);

        Route::get('/student-fees', [StudentFeeController::class, 'index']);
        Route::post('/student-fees', [StudentFeeController::class, 'store']);
        Route::post('/student-fees/assign', [StudentFeeController::class, 'assignToStudent']);
        Route::get('/students/{studentId}/fees', [StudentFeeController::class, 'getStudentFees']);
        Route::delete('/student-fees/clear-grade', [StudentFeeController::class, 'clearGradeStudentFees']);
        Route::get('/student-fees/{id}', [StudentFeeController::class, 'show']);
        Route::put('/student-fees/{id}', [StudentFeeController::class, 'update']);
        Route::put('/student-fees/{id}/override', [StudentFeeController::class, 'overrideAmount']);
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
        Route::get('/fee-payments/{id}/receipt', [FeePaymentController::class, 'receipt']);
        Route::get('/students/{studentId}/payments', [FeePaymentController::class, 'studentPayments']);
        Route::delete('/fee-payments/{id}', [FeePaymentController::class, 'destroy']);

        Route::get('/bank-accounts', [BankAccountController::class, 'index']);
        Route::post('/bank-accounts', [BankAccountController::class, 'store']);
        Route::get('/bank-accounts/{id}', [BankAccountController::class, 'show']);
        Route::put('/bank-accounts/{id}', [BankAccountController::class, 'update']);
        Route::patch('/bank-accounts/{id}', [BankAccountController::class, 'update']);
        Route::delete('/bank-accounts/{id}', [BankAccountController::class, 'destroy']);
        Route::post('/bank-accounts/{id}/set-default', [BankAccountController::class, 'setDefault']);

        Route::get('/exam-types', [ExamTypeController::class, 'index']);
        Route::post('/exam-types', [ExamTypeController::class, 'store']);
        Route::get('/exam-types/{id}', [ExamTypeController::class, 'show']);
        Route::put('/exam-types/{id}', [ExamTypeController::class, 'update']);
        Route::delete('/exam-types/{id}', [ExamTypeController::class, 'destroy']);

        Route::get('/grade-subjects', [GradeSubjectController::class, 'index']);
        Route::post('/grade-subjects', [GradeSubjectController::class, 'store']);
        Route::get('/grade-subjects/{id}', [GradeSubjectController::class, 'show']);
        Route::put('/grade-subjects/{id}', [GradeSubjectController::class, 'update']);
        Route::delete('/grade-subjects/{id}', [GradeSubjectController::class, 'destroy']);
        Route::get('/grades/{gradeId}/subjects', [GradeSubjectController::class, 'getByGrade']);

        Route::get('/exams', [ExamController::class, 'index']);
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
