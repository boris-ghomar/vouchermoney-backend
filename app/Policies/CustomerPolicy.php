<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Permission;
use App\Models\User;

class CustomerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::CUSTOMERS_VIEW);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Customer $customer): bool
    {
        if (
            $user->can(Permission::CUSTOMERS_VIEW) || (
                $user->customer_id === $customer->id &&
                $user->can(Permission::CUSTOMER_VIEW)
            )
        ) return true;

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_super;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Customer $customer): bool
    {
        return $user->is_super || (
            $user->customer_id === $customer->id && $user->is_customer_admin
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(): bool
    {
        return $this->delete();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function replicate(): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(): bool
    {
        return $this->delete();
    }
}
