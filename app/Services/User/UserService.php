<?php

namespace App\Services\User;

use App\Models\Customer\Customer;
use App\Models\Role;
use App\Models\User;
use App\Services\User\Contracts\UserServiceContract;

class UserService implements UserServiceContract
{
    public function createForCustomer(Customer $customer, string $name, string $email, string $password): User
    {
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->customer()->associate($customer);
        $user->password = $password;
        $user->save();

        $user->syncRoles(Role::CUSTOMER_ADMIN);

        return $user;
    }
}
