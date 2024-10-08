<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\Transaction\ArchivedTransaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ArchivedTransactionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_super || $user->is_customer_admin || $user->canAny(Permission::TRANSACTIONS_VIEW, Permission::CUSTOMER_TRANSACTIONS_VIEW);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ArchivedTransaction $transaction): bool
    {
        return $user->is_super || $user->can(Permission::TRANSACTIONS_VIEW) || ($transaction->customer_id === $user->customer_id && ($user->is_customer_admin || $user->can(Permission::CUSTOMER_TRANSACTIONS_VIEW)));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ArchivedTransaction $transaction): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ArchivedTransaction $transaction): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ArchivedTransaction $transaction): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ArchivedTransaction $transaction): bool
    {
        return false;
    }
}
