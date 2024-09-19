<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @property string $title
 */
class Role extends SpatieRole
{
    public const SUPER_ADMIN = "super-admin";
    public const MERCHANT = "merchant";
    public const RESELLER = "reseller";

    public function getTitleAttribute(): string
    {
        return __("roles." . $this->name);
    }
}
