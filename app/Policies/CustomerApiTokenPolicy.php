<?php

namespace App\Policies;

use App\Models\CustomerApiToken;
use App\Models\Permission;
use App\Models\User;

class CustomerApiTokenPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::CUSTOMERS_VIEW) || $user->is_customer_admin;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CustomerApiToken $token): bool
    {
        return $user->can(Permission::CUSTOMERS_VIEW) ||
            ($user->is_customer_admin && $user->customer_id === $token->customer_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_customer_admin;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CustomerApiToken $token): bool
    {
        return $user->is_customer_admin && $user->customer_id === $token->customer_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CustomerApiToken $token): bool
    {
        return $user->is_customer_admin && $user->customer_id === $token->customer_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(): bool
    {
        return false;
    }
}
