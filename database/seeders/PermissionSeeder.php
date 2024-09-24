<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'customer:view-any',
            'customer:create',
            'customer:update',
            'customer:delete',
            'user:view-any',
            'user:create',
            'user:delete',
            'user:attach-permission',
            'finance:request',
            'finance:resolve',
        ];

        foreach (array_merge($permissions, Permission::getCustomerPermissions()) as $permission) {
            Permission::create(['name' => $permission]);
        }

        $user = new User();
        $user->name = "Admin";
        $user->email = "admin@test.com";
        $user->email_verified_at = now();
        $user->role = User::ROLE_ADMIN;
        $user->password = Hash::make("123123123");
        $user->save();

        $user->syncPermissions($permissions);
    }
}
