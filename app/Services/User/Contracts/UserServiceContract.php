<?php

namespace App\Services\User\Contracts;

use App\Models\Customer\Customer;
use App\Models\User;

interface UserServiceContract
{
    /**
     * Create a new user associated with a specific customer.
     *
     * This method registers a new user for the given customer by accepting
     * the required details such as name, email, and password.
     * The newly created user will have access privileges based on the customer's
     * permissions and role.
     *
     * @param Customer $customer The customer instance associated with the new user.
     * @param string $name The name of the user.
     * @param string $email The email address of the user.
     * @param string $password The password for the user's account.
     *
     * @return User The newly created user instance associated with the customer.
     */
    public function createForCustomer(Customer $customer, string $name, string $email, string $password): User;
}
