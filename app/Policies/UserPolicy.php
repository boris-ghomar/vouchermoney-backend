<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_admin && ($user->can("user:view-any") || $user->is_customer && $user->can("customer:user:view-any"));
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if (
            $user->id === $model->id ||
            ($user->is_admin && $user->can("user:view-any")) ||
            ($user->is_customer && $user->customer_id === $model->customer_id && $user->can("customer:user:view-any"))
        ) return true;

        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can("user:create") || $user->can("customer:user:create");
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return
            ($user->is_customer && ($model->customer_id === $user->customer_id && $user->can("customer:user:delete"))) ||
            ($user->is_admin && $user->can("user:delete"));
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $this->update($user, $model);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $this->delete($user, $model);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function replicate(): bool
    {
        return false;
    }

    public function attachPermission(User $user, User $model, Permission $permission): bool
    {
        return $user->can($permission->name) && (
            ($user->is_customer && $user->customer_id === $model->customer_id && $user->can("customer:user:attach-permission")) ||
            ($user->is_admin && $user->can("user:attach-permission"))
        );
    }

    public function detachPermission(User $user, User $model, Permission $permission): bool
    {
        return $user->can($permission->name) && (
                ($user->is_customer && $user->customer_id === $model->customer_id && $user->can("customer:user:attach-permission")) ||
                ($user->is_admin && $user->can("user:attach-permission"))
            );
    }
}
