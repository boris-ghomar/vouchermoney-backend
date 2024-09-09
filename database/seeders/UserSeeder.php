<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            "super-admin",
            "admin",
            "merchant",
            "reseller"
        ];

        foreach ($roles as $role) {
            Role::create(["name" => $role]);
        }

        $permissions = [

        ];

        foreach ($permissions as $permission) {
            Permission::create(["name" => $permission]);
        }

        $users = [];

        foreach ($users as $user) {
            $user = new User();
        }
    }
}
