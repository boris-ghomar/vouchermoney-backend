<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = "123123123";
        $now = now();

        $customers = [
            "Apple" => [
                "users" => ["Roland", "Poland", "Holand", "Coland", "Sholand"],
                "role" => Role::MERCHANT
            ],
            "Microsoft" => [
                "users" => ["Tatar", "Lantar", "Cantar", "Bantar", "Hantar"],
                "role" => Role::MERCHANT
            ],
            "Ghomar" => [
                "users" => ["Lunar", "Tunar", "Bunar", "Punar"],
                "role" => Role::RESELLER
            ],
            "Superman" => [
                "users" => ["Admin", "Arvin", "Marvin", "Garvin", "Harvin"],
                "role" => Role::SUPER_ADMIN
            ]
        ];

        foreach($customers as $name => $customer) {
            $domain = "@" . strtolower($name) . ".com";

            $user = new User();
            $user->name = "Super Admin";
            $user->email = "super" . $domain;
            $user->password = Hash::make($password);
            $user->email_verified_at = $now;
            $user->save();

            $user->assignRole($customer["role"]);

            if ($customer["role"] !== Role::SUPER_ADMIN) {
                $obj = new Customer();
                $obj->name = $name;
                $obj->user_id = $user->id;
                $obj->save();
            }

            foreach($customer["users"] as $child) {
                $obj = new User();
                $obj->name = $child;
                $obj->email = strtolower($child) . $domain;
                $obj->password = Hash::make($password);
                $obj->email_verified_at = $now;
                $obj->parent_id = $user->id;
                $obj->save();
            }
        }
    }
}
