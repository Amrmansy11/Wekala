<?php

namespace App\Repositories\Consumer;

use App\Models\User;
use App\Helpers\AppHelper;
use App\Models\VendorUser;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class ConsumerRepository extends BaseRepository
{
    /**
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }
    /**
     * @param string $identifier
     * @param string $password
     * @return User|null
     */
    public function login(string $identifier, string $password): ?Model
    {
        $user = $this->query()
            ->where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    /**
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        $consumerData = collect($data)->only([
            'name',
            'email',
            'phone',
            'password',
            'birthday',
        ])->all();
        /** @var User $user */
        return parent::store($consumerData);
    }



    public function requestOTP($data, $user = null): int
    {
        return AppHelper::sendOtp($data, $user);
    }



    /**
     * @return User|null
     */
    public function resetPassword($phone, $password): ?Model
    {
        $consumer = $this->query()
            ->where('phone', $phone)
            ->first();
        if (!$consumer) {
            return null;
        }
        $consumer->update([
            'password' => Hash::make($password),
        ]);
        return $consumer;
    }
}
