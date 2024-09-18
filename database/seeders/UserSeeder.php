<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

//        $roles = [
//            Role::SUPER_ADMIN,
//            Role::ADMIN,
//            Role::MERCHANT,
//            Role::RESELLER
//        ];
//
//        foreach ($roles as $role) {
//            Role::create(["name" => $role]);
//        }
//
//        $permissions = [
//
//        ];
//
//        foreach ($permissions as $permission) {
//            Permission::create(["name" => $permission]);
//        }

        $users = [
            [
                "name" => "Superman",
                "email" => "super@test.com",
                "password" => "123123123",
                "role" => Role::SUPER_ADMIN
            ]
        ];

        foreach ($users as $user) {
            $obj = new User();

            $obj->name = $user["name"];
            $obj->email = $user["email"];
            $obj->email_verified_at = $now;
            $obj->password = Hash::make($user["password"]);
            $obj->save();

            $role = Role::findByName($user["role"]);
            $obj->assignRole($role);
        }
    }
}
