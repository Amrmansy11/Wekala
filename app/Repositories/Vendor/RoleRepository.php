<?php

namespace App\Repositories\Vendor;

use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class RoleRepository extends BaseRepository
{
    /**
     * @param Role $model
     */
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    public function store(array $data): Model
    {
        $roleData = collect($data)->only([
            'name',
            'guard_name'
        ])->all();
        /** @var Role $role */
        $role = parent::store($roleData);
        $role->givePermissionTo($data['permissions']);
        return $role;
    }

    public function update(array $data, int|string $modelId): Model
    {
        $roleData = collect($data)->only([
            'name',
            'guard_name'
        ])->all();
        /** @var Role $role */
        $role = parent::update($roleData, $modelId);
        $role->syncPermissions($data['permissions']);
        return $role;
    }
}
