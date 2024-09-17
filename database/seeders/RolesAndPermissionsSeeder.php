<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'customer.create',
            'customer.update',
            'customer.delete',
            'customer.view-any',
            'customer.detail',
            'user.create',
            'user.update',
            'user.details',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = [
            'super-admin' => ['customer.create', 'customer.update', 'customer.delete', 'customer.view-any', 'customer.detail', 'user.create', 'user.update', 'user.details'],
            'reseller' => ['customer.detail', 'user.create', 'user.update', 'user.details'],
            'merchant' => ['customer.detail', 'user.create', 'user.update', 'user.details'],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }
    }
}
