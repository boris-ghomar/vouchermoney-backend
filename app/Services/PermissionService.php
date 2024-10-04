<?php

namespace App\Services;

use App\Exceptions\AttemptToRedeemFrozenVoucher;
use App\Exceptions\NotAuthorized;
use App\Models\Permission;
use App\Models\User;
use App\Models\Voucher\Voucher;

class PermissionService
{
    public static function authorizedToRedeemVoucher(): bool
    {

    }

    /**
     * @throws NotAuthorized
     * @throws AttemptToRedeemFrozenVoucher
     */
    public static function canUserRedeemVoucher(User $user, Voucher $voucher): void
    {
        if (! $voucher->canBeRedeemed())
            throw new AttemptToRedeemFrozenVoucher();

        if ($user->is_customer_admin || $user->can(Permission::CUSTOMER_VOUCHER_REDEEM))
            return;

        throw new NotAuthorized();
    }

    public static function authorizedToFreezeVoucher(User $user, Voucher $voucher): bool
    {
        if ($user->is_admin) return false;

        if (! $user->is_customer_admin || ! $user->can(Permission::CUSTOMER_VOUCHER_FREEZE))
            return false;

        return $user->customer_id === $voucher->customer_id;
    }

    public static function authorizedToGenerateVoucher(User $user): bool
    {
        if ($user->is_admin) return false;

        return $user->is_customer_admin || $user->can(Permission::CUSTOMER_VOUCHER_GENERATE);
    }
}
