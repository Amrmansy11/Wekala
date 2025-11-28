<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminRoleAndPermissionSeeder extends Seeder
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
                'prefix' => 'admin_users',
                'group' => 'admin_users',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'roles',
                'group' => 'roles',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'permissions',
                'group' => 'permissions',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'category',
                'group' => 'category',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'materials',
                'group' => 'materials',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'brands',
                'group' => 'brands',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'packing_units',
                'group' => 'packing_units',
            ],
            [

                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'sizes',
                'group' => 'sizes',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'colors',
                'group' => 'colors',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'tags',
                'group' => 'tags',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'states',
                'group' => 'states',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'cities',
                'group' => 'cities',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'vendors',
                'group' => 'vendors',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'policies',
                'group' => 'policies',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'elwekala_collections',
                'group' => 'elwekala_collections',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'flash_sales',
                'group' => 'flash_sales',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'governments',
                'group' => 'governments',
            ],
            [
                'permissions' => ['view', 'create', 'update', 'delete'],
                'prefix' => 'delivery_areas',
                'group' => 'delivery_areas',
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
            $name = "{$prefix}_{$perm}";
            Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
        }
    }

    private function superAdminRole(): void
    {
        $this->command->info('Checking if super admin Role ...');

        /** @var Role $role */
        $role = Role::query()->firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'admin']);

        $role->givePermissionTo(Permission::query()->where('guard_name', 'admin')->get());
    }
}
