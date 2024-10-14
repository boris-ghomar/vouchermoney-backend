<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use App\Models\Voucher\ArchivedVoucher;

class ArchivedVoucherPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->canAny([Permission::VOUCHERS_VIEW, Permission::CUSTOMER_VOUCHER_VIEW]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ArchivedVoucher $voucher): bool
    {
        if ($user->can(Permission::VOUCHERS_VIEW) || $user->can([Permission::CUSTOMER_VOUCHER_VIEW])) return true;

        return false;
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
