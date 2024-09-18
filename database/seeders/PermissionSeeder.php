<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'reseller' => ['customer.detail', 'user.create', 'user.update', 'user.details'],
            'merchant' => ['customer.detail', 'user.create', 'user.update', 'user.details'],
        ];

        foreach ($roles as $name => $permissions) {
            $role = Role::firstOrCreate(['name' => $name]);
            $role->syncPermissions($permissions);
        }
    }
}
