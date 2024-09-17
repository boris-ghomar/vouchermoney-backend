<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Role;
use App\Models\User;

class CustomerPolicy
{
    private function isAdmin(User $user): bool
    {
        return $user->hasRole(Role::ADMIN);
    }

    public function viewAny(User $user): bool
    {
        return $user->can("customer.view-any");
    }

    public function view(User $user, Customer $customer): bool
    {
        return $customer->isChild($user);
    }

    public function create(User $user, ?Customer $customer): bool
    {
        return $user->can("customer.create") && (
                $this->isAdmin($user) ||
                ($customer && $customer->isChild($user))
            );
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->can("customer.update") && (
                $this->isAdmin($user) ||
                $customer->isChild($user)
            );
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->can("customer.delete") && ($this->isAdmin($user) || $customer->user->id === $user->id);
    }

    public function attachUser(User $user,Customer $customer): bool
    {
        return $user->can("user.create") && ($this->isAdmin($user)|| $customer->user->id === $user->id);
    }
}
