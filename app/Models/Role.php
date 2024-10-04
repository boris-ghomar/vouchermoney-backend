<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * Only for admins.
     * Define admin user who can do everything available for admins.
     */
    const SUPER_ADMIN = "super-admin";

    /**
     * Only for customers.
     * Define customer's administrator user, who can do everything available for customer.
     */
    const CUSTOMER_ADMIN = "customer-admin";
}
