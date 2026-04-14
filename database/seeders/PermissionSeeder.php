<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = $this->getPermissions();

        foreach ($permissions as $group => $groupPermissions) {
            foreach ($groupPermissions as $permission) {
                Permission::firstOrCreate(
                    ['slug' => $permission['slug']],
                    [
                        'name' => $permission['name'],
                        'group' => $group,
                        'description' => $permission['description'] ?? null,
                    ]
                );
            }
        }
    }

    private function getPermissions(): array
    {
        return [
            'users' => [
                ['slug' => 'users.view', 'name' => 'View Users', 'description' => 'Can view user list'],
                ['slug' => 'users.create', 'name' => 'Create Users', 'description' => 'Can create new users'],
                ['slug' => 'users.update', 'name' => 'Update Users', 'description' => 'Can update user details'],
                ['slug' => 'users.delete', 'name' => 'Delete Users', 'description' => 'Can delete users'],
                ['slug' => 'users.assign_role', 'name' => 'Assign Roles', 'description' => 'Can assign roles to users'],
                ['slug' => 'users.toggle_status', 'name' => 'Toggle Status', 'description' => 'Can activate/deactivate users'],
            ],
            'school' => [
                ['slug' => 'school.view', 'name' => 'View School', 'description' => 'Can view school structure'],
                ['slug' => 'school.update', 'name' => 'Update School', 'description' => 'Can update school settings'],
            ],
            'grades' => [
                ['slug' => 'grades.view', 'name' => 'View Grades', 'description' => 'Can view grades'],
                ['slug' => 'grades.create', 'name' => 'Create Grades', 'description' => 'Can create grades'],
                ['slug' => 'grades.update', 'name' => 'Update Grades', 'description' => 'Can update grades'],
                ['slug' => 'grades.delete', 'name' => 'Delete Grades', 'description' => 'Can delete grades'],
            ],
            'sections' => [
                ['slug' => 'sections.view', 'name' => 'View Sections', 'description' => 'Can view sections'],
                ['slug' => 'sections.create', 'name' => 'Create Sections', 'description' => 'Can create sections'],
                ['slug' => 'sections.update', 'name' => 'Update Sections', 'description' => 'Can update sections'],
                ['slug' => 'sections.delete', 'name' => 'Delete Sections', 'description' => 'Can delete sections'],
            ],
            'students' => [
                ['slug' => 'students.view', 'name' => 'View Students', 'description' => 'Can view student list'],
                ['slug' => 'students.view_own', 'name' => 'View Own Students', 'description' => 'Can view assigned students only'],
                ['slug' => 'students.create', 'name' => 'Create Students', 'description' => 'Can enroll new students'],
                ['slug' => 'students.update', 'name' => 'Update Students', 'description' => 'Can update student details'],
                ['slug' => 'students.delete', 'name' => 'Delete Students', 'description' => 'Can delete students'],
                ['slug' => 'students.export', 'name' => 'Export Students', 'description' => 'Can export student data'],
            ],
            'teachers' => [
                ['slug' => 'teachers.view', 'name' => 'View Teachers', 'description' => 'Can view teacher list'],
                ['slug' => 'teachers.create', 'name' => 'Create Teachers', 'description' => 'Can add new teachers'],
                ['slug' => 'teachers.update', 'name' => 'Update Teachers', 'description' => 'Can update teacher details'],
                ['slug' => 'teachers.delete', 'name' => 'Delete Teachers', 'description' => 'Can delete teachers'],
            ],
            'attendance' => [
                ['slug' => 'attendance.view', 'name' => 'View Attendance', 'description' => 'Can view attendance records'],
                ['slug' => 'attendance.view_own_class', 'name' => 'View Own Class Attendance', 'description' => 'Can view attendance for assigned classes'],
                ['slug' => 'attendance.mark', 'name' => 'Mark Attendance', 'description' => 'Can mark student attendance'],
                ['slug' => 'attendance.mark_own_class', 'name' => 'Mark Own Class Attendance', 'description' => 'Can mark attendance for assigned classes'],
                ['slug' => 'attendance.edit', 'name' => 'Edit Attendance', 'description' => 'Can edit past attendance'],
                ['slug' => 'attendance.export', 'name' => 'Export Attendance', 'description' => 'Can export attendance data'],
            ],
            'subjects' => [
                ['slug' => 'subjects.view', 'name' => 'View Subjects', 'description' => 'Can view subject list'],
                ['slug' => 'subjects.create', 'name' => 'Create Subjects', 'description' => 'Can create subjects'],
                ['slug' => 'subjects.update', 'name' => 'Update Subjects', 'description' => 'Can update subjects'],
                ['slug' => 'subjects.delete', 'name' => 'Delete Subjects', 'description' => 'Can delete subjects'],
            ],
            'exams' => [
                ['slug' => 'exams.view', 'name' => 'View Exams', 'description' => 'Can view exam schedules'],
                ['slug' => 'exams.create', 'name' => 'Create Exams', 'description' => 'Can create exam schedules'],
                ['slug' => 'exams.update', 'name' => 'Update Exams', 'description' => 'Can update exam details'],
                ['slug' => 'exams.delete', 'name' => 'Delete Exams', 'description' => 'Can delete exams'],
                ['slug' => 'exams.publish', 'name' => 'Publish Exams', 'description' => 'Can publish exam results'],
            ],
            'marks' => [
                ['slug' => 'marks.view', 'name' => 'View Marks', 'description' => 'Can view student marks'],
                ['slug' => 'marks.view_own', 'name' => 'View Own Marks', 'description' => 'Can view own marks (students)'],
                ['slug' => 'marks.view_own_class', 'name' => 'View Class Marks', 'description' => 'Can view marks for assigned classes'],
                ['slug' => 'marks.enter', 'name' => 'Enter Marks', 'description' => 'Can enter student marks'],
                ['slug' => 'marks.enter_own_subject', 'name' => 'Enter Own Subject Marks', 'description' => 'Can enter marks for assigned subjects'],
                ['slug' => 'marks.edit', 'name' => 'Edit Marks', 'description' => 'Can edit entered marks'],
                ['slug' => 'marks.approve', 'name' => 'Approve Marks', 'description' => 'Can approve marks'],
            ],
            'results' => [
                ['slug' => 'results.view', 'name' => 'View Results', 'description' => 'Can view exam results'],
                ['slug' => 'results.view_own', 'name' => 'View Own Results', 'description' => 'Can view own results (students)'],
                ['slug' => 'results.publish', 'name' => 'Publish Results', 'description' => 'Can publish results'],
                ['slug' => 'results.export', 'name' => 'Export Results', 'description' => 'Can export results'],
            ],
            'report_cards' => [
                ['slug' => 'report_cards.view', 'name' => 'View Report Cards', 'description' => 'Can view report cards'],
                ['slug' => 'report_cards.view_own', 'name' => 'View Own Report Cards', 'description' => 'Can view own report cards'],
                ['slug' => 'report_cards.generate', 'name' => 'Generate Report Cards', 'description' => 'Can generate report cards'],
                ['slug' => 'report_cards.print', 'name' => 'Print Report Cards', 'description' => 'Can print report cards'],
            ],
            'fees' => [
                ['slug' => 'fees.view', 'name' => 'View Fees', 'description' => 'Can view fee structures'],
                ['slug' => 'fees.create', 'name' => 'Create Fees', 'description' => 'Can create fee types'],
                ['slug' => 'fees.update', 'name' => 'Update Fees', 'description' => 'Can update fees'],
                ['slug' => 'fees.delete', 'name' => 'Delete Fees', 'description' => 'Can delete fees'],
                ['slug' => 'fees.assign', 'name' => 'Assign Fees', 'description' => 'Can assign fees to students'],
            ],
            'payments' => [
                ['slug' => 'payments.view', 'name' => 'View Payments', 'description' => 'Can view payment records'],
                ['slug' => 'payments.record', 'name' => 'Record Payments', 'description' => 'Can record fee payments'],
                ['slug' => 'payments.refund', 'name' => 'Refund Payments', 'description' => 'Can process refunds'],
                ['slug' => 'payments.export', 'name' => 'Export Payments', 'description' => 'Can export payment data'],
            ],
            'discounts' => [
                ['slug' => 'discounts.view', 'name' => 'View Discounts', 'description' => 'Can view discount types'],
                ['slug' => 'discounts.create', 'name' => 'Create Discounts', 'description' => 'Can create discounts'],
                ['slug' => 'discounts.update', 'name' => 'Update Discounts', 'description' => 'Can update discounts'],
                ['slug' => 'discounts.delete', 'name' => 'Delete Discounts', 'description' => 'Can delete discounts'],
                ['slug' => 'discounts.apply', 'name' => 'Apply Discounts', 'description' => 'Can apply discounts to students'],
            ],
            'reports' => [
                ['slug' => 'reports.view', 'name' => 'View Reports', 'description' => 'Can view reports'],
                ['slug' => 'reports.finance', 'name' => 'View Finance Reports', 'description' => 'Can view financial reports'],
                ['slug' => 'reports.academic', 'name' => 'View Academic Reports', 'description' => 'Can view academic reports'],
                ['slug' => 'reports.export', 'name' => 'Export Reports', 'description' => 'Can export reports'],
            ],
            'settings' => [
                ['slug' => 'settings.view', 'name' => 'View Settings', 'description' => 'Can view system settings'],
                ['slug' => 'settings.update', 'name' => 'Update Settings', 'description' => 'Can update system settings'],
            ],
            'roles_permissions' => [
                ['slug' => 'roles.view', 'name' => 'View Roles', 'description' => 'Can view roles'],
                ['slug' => 'roles.create', 'name' => 'Create Roles', 'description' => 'Can create roles'],
                ['slug' => 'roles.update', 'name' => 'Update Roles', 'description' => 'Can update roles'],
                ['slug' => 'roles.delete', 'name' => 'Delete Roles', 'description' => 'Can delete roles'],
            ],
        ];
    }
}
