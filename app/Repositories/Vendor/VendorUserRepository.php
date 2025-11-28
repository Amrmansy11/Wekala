<?php

namespace App\Repositories\Vendor;

use App\Helpers\AppHelper;
use App\Models\VendorUser;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class VendorUserRepository extends BaseRepository
{
    /**
     * @param VendorUser $model
     */
    public function __construct(VendorUser $model)
    {
        parent::__construct($model);
    }

    /**
     * @param string $identifier
     * @param string $password
     * @return VendorUser|null
     */
    public function login(string $identifier, string $password): ?VendorUser
    {
        $VendorUser = $this->query()
            ->where('phone', $identifier)
            ->first();

        if (!$VendorUser || !Hash::check($password, $VendorUser->password)) {
            return null;
        }

        return $VendorUser;
    }

    /**
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        $VendorUserData = collect($data)->only([
            'name',
            'email',
            'phone',
            'is_active',
            'main_account',
            'vendor_id',
            'password',
        ])->all();
        /** @var VendorUser $VendorUser */
        $VendorUser = parent::store($VendorUserData);
        if (isset($data['roles']) || isset($data['permissions'])) {
            $this->assignRolesAndPermissions($VendorUser, $data);
        }
        return $VendorUser;
    }

    /**
     * @param array $data
     * @param int|string $modelId
     * @return Model
     */
    public function update(array $data, int|string $modelId): Model
    {
        $VendorUserData = collect($data)->only([
            'name',
            'email',
            'phone',
        ])->all();

        $VendorUser = parent::update($VendorUserData, $modelId);
        $VendorUser['vendor_id'] = $data['vendor_id'] ?? null;

        $this->assignRolesAndPermissions($VendorUser, $data);
        return $VendorUser;
    }

    public function requestOTP($data, $user = null): int
    {
        return AppHelper::sendOtp($data, $user);
    }

    /**
     * @param $VendorUser
     * @param $data
     * @return void
     */
    private function assignRolesAndPermissions($VendorUser, $data): void
    {
        $roles = $data['roles'] ?? [];
        $VendorUser->syncRoles($roles);
        $permissions = collect($data['permissions'] ?? [])
            ->diff($VendorUser->getPermissionsViaRoles()->pluck('name'));
        $VendorUser->syncPermissions($permissions);
    }

    /**
     * @return VendorUser|null
     */
    public function resetPassword($phone, $password): ?Model
    {
        $VendorUser = $this->query()
            ->where('phone', $phone)
            ->first();
        if (!$VendorUser) {
            return null;
        }
        $VendorUser->update([
            'password' => Hash::make($password),
        ]);
        return $VendorUser;
    }
}
