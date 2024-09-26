<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    public static function getCustomerPermissions(): array
    {
        return [
            "customer:user:view-any",
            "customer:user:create",
            "customer:user:delete",
            "customer:user:attach-permission",
            "customer:view-balance",
            "customer:finance",
            "customer:notifications"
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
            "customer:user:create"
        ];
    }
}
