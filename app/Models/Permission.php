<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * High-order.
     * Allow to update customer's "name" and "avatar" fields.
     * By default, all customer's administrators have this permission.
     */
    const CUSTOMER_UPDATE = "customer:update";

    /**
     * High-order
     * Allow to view, create and delete customer's users
     * By default, all customer's administrators have this permission.
     */
    const CUSTOMER_USER_MANAGEMENT = "customer:user:management";


    public static function getCustomerPermissions(): array
    {
        return [
            static::CUSTOMER_UPDATE,
            "customer:user:view-any",
            "customer:user:create",
            "customer:user:delete",
            "customer:user:attach-permission",
            "customer:view-balance",
            "customer:finance",
            "customer:notifications",
            "customer:voucher:generate",
            "customer:voucher:redeem",
            "customer:voucher:freeze",
            "customer:voucher:view",
            "customer:transaction:view"
        ];
    }

    public static function getAdminPermissions(): array
    {
        return [
            'customer:management',
            'customer:view-any',
            'customer:create',
            'customer:delete',
            'user:view-any',
            'user:create',
            'user:delete',
            'user:attach-permission',
            'finance:request',
            'finance:resolve',
            'voucher:view',
            'activity:view',
            'transaction:view'
        ];
    }

    public static function getAllHighOrderPermissions(): array
    {
        return [
            "customer:delete",
            "customer:update",
            "user:delete",
            "user:attach-permission",
            "customer:user:delete",
            "customer:user:attach-permission",
            "customer:user:create",
        ];
    }
}
