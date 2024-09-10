<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public const SUPER_ADMIN = "super-admin";
    public const ADMIN = "admin";
    public const MERCHANT = "merchant";
    public const RESELLER = "reseller";
}
