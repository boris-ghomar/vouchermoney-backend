<?php

namespace App\Policies;

use App\Models\User;

class PermissionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin || $user->is_customer_admin;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function replicate(): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(): bool
    {
        return false;
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
