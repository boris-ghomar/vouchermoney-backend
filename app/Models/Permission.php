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
        ];
    }
}
