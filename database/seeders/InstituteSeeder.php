<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InstituteSeeder extends Seeder
{
    public function run(): void
    {
        $exists = DB::table('institutes')->exists();

        if ($exists) {
            $this->command->info('Institute already exists, skipping.');
            return;
        }

        $adminEmail = 'admin@eschool.pk';

        $userId = DB::table('users')->insertGetId([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => $adminEmail,
            'password' => Hash::make('password123'),
            'user_type' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $instituteId = DB::table('institutes')->insertGetId([
            'name' => 'Demo School',
            'address' => 'Lahore, Pakistan',
            'contact_phone' => '042-1234567',
            'contact_email' => $adminEmail,
            'status' => 'approved',
            'admin_user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->where('id', $userId)->update(['institute_id' => $instituteId]);

        $adminRole = DB::table('roles')->where('slug', 'admin')->first();
        if ($adminRole) {
            DB::table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $adminRole->id,
                'assigned_by' => null,
                'assigned_at' => now(),
            ]);
        }

        $this->command->info("Created institute: Demo School (ID: {$instituteId})");
        $this->command->info("Created admin user: admin@eschool.pk / password123");
        $this->command->info("Assigned 'admin' role to user");
    }
}
