<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = $this->getRoles();

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description'] ?? null,
                    'sort_order' => $roleData['sort_order'] ?? 0,
                ]
            );

            if (isset($roleData['permissions'])) {
                $permissions = Permission::whereIn('slug', $roleData['permissions'])->pluck('id');
                $role->permissions()->sync($permissions);
            }
        }
    }

    private function getRoles(): array
    {
        return [
            [
                'slug' => 'super_admin',
                'name' => 'Super Admin',
                'description' => 'Full system access across all institutes',
                'sort_order' => 1,
                'permissions' => ['*'],
            ],
            [
                'slug' => 'admin',
                'name' => 'Admin',
                'description' => 'Full institute access',
                'sort_order' => 2,
                'permissions' => [
                    'users.view', 'users.create', 'users.update', 'users.assign_role', 'users.toggle_status',
                    'school.view', 'school.update',
                    'grades.view', 'grades.create', 'grades.update', 'grades.delete',
                    'sections.view', 'sections.create', 'sections.update', 'sections.delete',
                    'students.view', 'students.create', 'students.update', 'students.delete', 'students.export',
                    'teachers.view', 'teachers.create', 'teachers.update', 'teachers.delete',
                    'attendance.view', 'attendance.mark', 'attendance.edit', 'attendance.export',
                    'subjects.view', 'subjects.create', 'subjects.update', 'subjects.delete',
                    'exams.view', 'exams.create', 'exams.update', 'exams.delete', 'exams.publish',
                    'marks.view', 'marks.enter', 'marks.edit', 'marks.approve',
                    'results.view', 'results.publish', 'results.export',
                    'report_cards.view', 'report_cards.generate', 'report_cards.print',
                    'fees.view', 'fees.create', 'fees.update', 'fees.delete', 'fees.assign',
                    'payments.view', 'payments.record', 'payments.refund', 'payments.export',
                    'discounts.view', 'discounts.create', 'discounts.update', 'discounts.delete', 'discounts.apply',
                    'reports.view', 'reports.finance', 'reports.academic', 'reports.export',
                    'settings.view', 'settings.update',
                    'roles.view', 'roles.create', 'roles.update', 'roles.delete',
                ],
            ],
            [
                'slug' => 'principal',
                'name' => 'Principal',
                'description' => 'School principal with management access',
                'sort_order' => 3,
                'permissions' => [
                    'school.view', 'school.update',
                    'grades.view', 'sections.view',
                    'students.view', 'students.export',
                    'teachers.view',
                    'attendance.view', 'attendance.export',
                    'subjects.view',
                    'exams.view', 'exams.publish',
                    'marks.view', 'marks.approve',
                    'results.view', 'results.publish', 'results.export',
                    'report_cards.view', 'report_cards.print',
                    'fees.view', 'fees.assign',
                    'payments.view', 'payments.export',
                    'reports.view', 'reports.finance', 'reports.academic', 'reports.export',
                ],
            ],
            [
                'slug' => 'teacher',
                'name' => 'Teacher',
                'description' => 'Teaching staff with class access',
                'sort_order' => 4,
                'permissions' => [
                    'students.view_own', 'students.view',
                    'attendance.view_own_class', 'attendance.mark_own_class',
                    'subjects.view',
                    'exams.view',
                    'marks.view_own_class', 'marks.enter_own_subject',
                    'results.view',
                    'report_cards.view',
                    'reports.academic',
                ],
            ],
            [
                'slug' => 'student',
                'name' => 'Student',
                'description' => 'Student portal access',
                'sort_order' => 5,
                'permissions' => [
                    'attendance.view_own',
                    'marks.view_own',
                    'results.view_own',
                    'report_cards.view_own',
                ],
            ],
            [
                'slug' => 'parent',
                'name' => 'Parent',
                'description' => 'Parent portal access for children',
                'sort_order' => 6,
                'permissions' => [
                    'students.view_own',
                    'attendance.view_own',
                    'marks.view_own',
                    'results.view_own',
                    'report_cards.view_own',
                    'fees.view',
                    'payments.view',
                ],
            ],
            [
                'slug' => 'accountant',
                'name' => 'Accountant',
                'description' => 'Finance department staff',
                'sort_order' => 7,
                'permissions' => [
                    'students.view',
                    'fees.view', 'fees.create', 'fees.update', 'fees.assign',
                    'payments.view', 'payments.record', 'payments.refund', 'payments.export',
                    'discounts.view', 'discounts.create', 'discounts.update', 'discounts.apply',
                    'reports.view', 'reports.finance', 'reports.export',
                ],
            ],
            [
                'slug' => 'librarian',
                'name' => 'Librarian',
                'description' => 'Library management staff',
                'sort_order' => 8,
                'permissions' => [
                    'students.view',
                ],
            ],
        ];
    }
}
