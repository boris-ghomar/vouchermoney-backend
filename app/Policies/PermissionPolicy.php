<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Role;
use App\Models\User;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can("role.view-any");
    }

    public function view(User $user, Role $role): bool
    {
        if ($role->name === Role::SUPER_ADMIN)
            return false;

        return true;
    }

    public function create(User $user, ?Customer $customer): bool
    {
        return false;
    }

    public function update(User $user, Customer $customer): bool
    {
        return false;
    }

    public function delete(User $user, Customer $customer): bool
    {
        return false;
    }
}
