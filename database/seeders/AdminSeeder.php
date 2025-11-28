<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'admin']
        );

        $admin = Admin::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'username' => 'superadmin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'is_active' => true,
                'password' => bcrypt('password'),
            ]
        );

        $admin->assignRole($role);
    }
}
