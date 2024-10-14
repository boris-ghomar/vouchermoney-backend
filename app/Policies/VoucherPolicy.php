<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use App\Models\Voucher\Voucher;

class VoucherPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->canAny([
            Permission::VOUCHERS_VIEW,
            Permission::CUSTOMER_VOUCHER_VIEW,
            Permission::CUSTOMER_VOUCHER_FREEZE
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Voucher $voucher): bool
    {
        if ($user->can(Permission::VOUCHERS_VIEW) || $user->canAny([Permission::CUSTOMER_VOUCHER_VIEW, Permission::CUSTOMER_VOUCHER_FREEZE])) return true;

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->can(Permission::CUSTOMER_VOUCHER_GENERATE)) return true;

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Voucher $voucher): bool
    {
        if ($user->is_admin) return false;

        if (
            $user->can(Permission::CUSTOMER_VOUCHER_FREEZE) &&
            $user->customer_id === $voucher->customer_id
        ) return true;

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
