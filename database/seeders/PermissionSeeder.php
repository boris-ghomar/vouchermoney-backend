<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'role.view-any',
            'permission.view-any',
            'customer.view-any',
            'user.view-any',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [Role::SUPER_ADMIN, Role::MERCHANT, Role::RESELLER];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        $rolePermissions = ["user.view-any"];

        foreach ([Role::MERCHANT, Role::RESELLER] as $role) {
            Role::findByName($role)->syncPermissions($rolePermissions);
        }
    }
}
