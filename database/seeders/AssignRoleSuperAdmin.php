<?php

namespace Database\Seeders;

use App\Models\VendorUser;
use Illuminate\Database\Seeder;

class AssignRoleSuperAdmin extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendorUser = VendorUser::whereEmail('h&m@gmail.com')->first();
        if ($vendorUser && !$vendorUser->hasRole('Super Admin')) {
            $vendorUser->assignRole('Super Admin');
        }
    }
}
