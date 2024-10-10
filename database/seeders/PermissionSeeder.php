<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            Permission::CUSTOMERS_VIEW,
            Permission::VOUCHERS_VIEW,
            Permission::TRANSACTIONS_VIEW,
            Permission::FINANCES_VIEW,
            Permission::FINANCES_MANAGEMENT,
            Permission::ACTIVITY_VIEW,
            Permission::CUSTOMER_VIEW,
            Permission::CUSTOMER_USER_VIEW,
            Permission::CUSTOMER_FINANCE,
            Permission::CUSTOMER_VOUCHER_VIEW,
            Permission::CUSTOMER_VOUCHER_GENERATE,
            Permission::CUSTOMER_VOUCHER_REDEEM,
            Permission::CUSTOMER_VOUCHER_FREEZE,
            Permission::CUSTOMER_TRANSACTIONS_VIEW
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $user = new User();
        $user->name = "Administrator";
        $user->email = "admin@test.com";
        $user->email_verified_at = now();
        $user->password = "123123123";
        $user->save();

        $roles = [
            Role::SUPER_ADMIN => Permission::$adminPermissions,
            Role::CUSTOMER_ADMIN => Permission::$customerPermissions
        ];

        foreach ($roles as $name => $permissions) {
            $role = new Role();
            $role->name = $name;
            $role->save();

            $role->syncPermissions($permissions);
        }

        $user->assignRole(Role::SUPER_ADMIN);
    }
}
