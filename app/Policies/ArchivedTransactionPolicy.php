<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\Transaction\ArchivedTransaction;
use App\Models\User;

class ArchivedTransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->canAny([Permission::TRANSACTIONS_VIEW, Permission::CUSTOMER_TRANSACTIONS_VIEW]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ArchivedTransaction $transaction): bool
    {
        return $user->can(Permission::TRANSACTIONS_VIEW) ||
            ($transaction->customer_id === $user->customer_id && $user->can(Permission::CUSTOMER_TRANSACTIONS_VIEW));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(): bool
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
