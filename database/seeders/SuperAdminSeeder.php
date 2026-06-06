<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@eschool.pk'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => 'admin123',
                'user_type' => 'super_admin',
                'status' => 'active',
            ]
        );
    }
}
