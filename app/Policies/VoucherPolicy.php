<?php

namespace App\Policies;

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
            "voucher:view",
            "customer:voucher:view",
            "customer:voucher:generate",
            "customer:voucher:redeem",
            "customer:voucher:freeze",
        ]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Voucher $voucher): bool
    {
        if (
            $user->is_customer &&
            $user->can("customer:voucher:view") &&
            $voucher->customer_id === $user->customer->id
        ) return true;

        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->is_customer && $user->can("customer:voucher:generate"))
            return true;

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Voucher $voucher): bool
    {
        if ($user->customer_id === $voucher->customer_id)
            return true;

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
