<?php

namespace App\Repositories\Admin;

use App\Models\Admin;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserRepository extends BaseRepository
{
    /**
     * @param Admin $model
     */
    public function __construct(Admin $model)
    {
        parent::__construct($model);
    }

    /**
     * @param string $identifier
     * @param string $password
     * @return Admin|null
     */
    public function login(string $identifier, string $password): ?Admin
    {
        $admin = $this->query()
            ->where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (!$admin || !Hash::check($password, $admin->password)) {
            return null;
        }

        return $admin;
    }

    /**
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        $adminData = collect($data)->only([
            'username',
            'email',
            'first_name',
            'last_name',
            'is_active',
        ])->all();
        $adminData['password'] = Hash::make(Str::random());
        /** @var Admin $admin */
        $admin = parent::store($adminData);
        $this->assignRolesAndPermissions($admin, $data);
        return $admin;
    }

    /**
     * @param array $data
     * @param int|string $modelId
     * @return Model
     */
    public function update(array $data, int|string $modelId): Model
    {
        $adminData = collect($data)->only([
            'username',
            'email',
            'first_name',
            'last_name',
            'is_active',
        ])->all();

        $admin = parent::update($adminData, $modelId);
        $this->assignRolesAndPermissions($admin, $data);
        return $admin;
    }

    /**
     * @param $admin
     * @param $data
     * @return void
     */
    private function assignRolesAndPermissions($admin, $data): void
    {
        $roles = $data['roles'] ?? [];
        $admin->syncRoles($roles);
        $permissions = collect($data['permissions'] ?? [])
            ->diff($admin->getPermissionsViaRoles()->pluck('name'));
        $admin->syncPermissions($permissions);
    }
}
