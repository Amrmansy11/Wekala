<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class VendorsRoleAndPermissionSeeder extends Seeder
{

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createPermissions();
        $this->superAdminRole();
    }

    private function createPermissions(): void
    {
        $permissions = [
            [
                'permissions' => ['view'],
                'prefix' => 'dashboard',
                'group' => 'Dashboard',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'brands',
                'group' => 'brands',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'branch',
                'group' => 'branch',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'branch_user',
                'group' => 'branch_user',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'vendor_users',
                'group' => 'vendor_users',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'roles',
                'group' => 'roles',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'feeds',
                'group' => 'feeds',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'offers',
                'group' => 'offers',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'stories',
                'group' => 'stories',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'products',
                'group' => 'products',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'delivery_areas',
                'group' => 'delivery_areas',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'vouchers',
                'group' => 'vouchers',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'points',
                'group' => 'points',
            ],

        ];

        foreach ($permissions as $permission) {
            $this->createPermissionsGroup(
                $permission['permissions'],
                $permission['prefix'],
                $permission['group'],
            );
        }
    }

    private function createPermissionsGroup(array $permissions, string $prefix, string $group = ''): void
    {
        $this->command->info("Creating {$group} Permissions ...");
        foreach ($permissions as $perm) {
            $name = "vendor_{$prefix}_{$perm}";
            Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'vendor']);
        }
    }

    private function superAdminRole(): void
    {
        $this->command->info('Checking if super admin Role ...');
        /** @var Role $role */
        $role = Role::query()->firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'vendor']);
        $role->givePermissionTo(Permission::query()->where('guard_name', 'vendor')->get());
    }
}
