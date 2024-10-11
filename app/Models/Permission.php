<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * @property-read  string  $description
 * @property-read  string  $description_long
 * @property-read  string  $name_label
 * @property-read  string  $name_title
 * @property-read  string  $title
 */
class Permission extends SpatiePermission
{
    use LogsActivity;

    /**
     * Only for admins.
     * Allow to see all customers.
     */
    const CUSTOMERS_VIEW = "customers:view";

    /**
     * Only for admins.
     * Allow to see all vouchers.
     */
    const VOUCHERS_VIEW = "vouchers:view";

    /**
     * Only for admins.
     * Allow to see all transactions.
     */
    const TRANSACTIONS_VIEW = "transactions:view";

    /**
     * Only for admins.
     * Allow to see all finance requests.
     */
    const FINANCES_VIEW = "finances:view";

    /**
     * Only for admins.
     * Allow to approve or reject finance request and make request for specific customer.
     * It also allows to see all finance requests except of archived finances.
     */
    const FINANCES_MANAGEMENT = "finances:management";

    /**
     * Only for admins.
     * Allow to see all activity logs.
     */
    const ACTIVITY_VIEW = "activity:view";

    /**
     * Only for customers.
     * Allow to see customer's info.
     */
    const CUSTOMER_VIEW = "customer:view";

    /**
     * Only for customers.
     * Allow to see all own users.
     */
    const CUSTOMER_USER_VIEW = "customer:user:view";

    /**
     * Only for customers.
     * Allow to see all active and archived finance requests.
     * Allow to make finance request and delete existing active request.
     */
    const CUSTOMER_FINANCE = "customer:finance";

    /**
     * Only for customers.
     * Allow to see all own vouchers.
     */
    const CUSTOMER_VOUCHER_VIEW = "customer:voucher:view";

    /**
     * Only for customers.
     * Allow to generate own vouchers.
     */
    const CUSTOMER_VOUCHER_GENERATE = "customer:voucher:generate";

    /**
     * Only for customers.
     * Allow to redeem vouchers.
     */
    const CUSTOMER_VOUCHER_REDEEM = "customer:voucher:redeem";

    /**
     * Only for customers.
     * Allow to freeze/unfreeze vouchers.
     * It also allows to see all own vouchers except of archived vouchers.
     */
    const CUSTOMER_VOUCHER_FREEZE = "customer:voucher:freeze";

    /**
     * Only for customers.
     * Allow to see all customer's transactions.
     */
    const CUSTOMER_TRANSACTIONS_VIEW = "customer:transactions:view";

    /**
     * All customers can have these permissions.
     */
    public static array $customerPermissions = [
        self::CUSTOMER_VIEW,
        self::CUSTOMER_USER_VIEW,
        self::CUSTOMER_FINANCE,
        self::CUSTOMER_VOUCHER_VIEW,
        self::CUSTOMER_VOUCHER_GENERATE,
        self::CUSTOMER_VOUCHER_REDEEM,
        self::CUSTOMER_VOUCHER_FREEZE,
        self::CUSTOMER_TRANSACTIONS_VIEW
    ];

    /**
     * Api token can have these permissions.
     * @var array|string[]
     */
    public static array $apiTokenPermissions = [
        self::CUSTOMER_VOUCHER_VIEW,
        self::CUSTOMER_VOUCHER_GENERATE,
        self::CUSTOMER_VOUCHER_REDEEM,
        self::CUSTOMER_VOUCHER_FREEZE,
    ];

    /**
     * All admins can have these permissions.
     */
    public static array $adminPermissions = [
        self::CUSTOMERS_VIEW,
        self::VOUCHERS_VIEW,
        self::TRANSACTIONS_VIEW,
        self::FINANCES_VIEW,
        self::FINANCES_MANAGEMENT,
        self::ACTIVITY_VIEW
    ];

    public static function getApiTokenPermissions(): array
    {
        $permissions = static::query()->whereIn("name", static::$apiTokenPermissions)->pluck('name', 'id');

        foreach ($permissions as $key => $permission) {
            $permissions[$key] = [
                'id' => $permission->id,
                'name' => $permission->name_label . $permission->description,
            ];
        }

        return $permissions;
    }

    public function getDescriptionAttribute(): string
    {
        return __("permissions." . $this->name . ".description.short");
    }

    public function getDescriptionLongAttribute(): string
    {
        return __("permissions." . $this->name . ".description.long");
    }

    public function getNameLabelAttribute(): string
    {
        return __("permissions." . $this->name . ".label");
    }

    public function getNameTitleAttribute(): string
    {
        return __("permissions." . $this->name . ".title");
    }

    public function getTitleAttribute(): string
    {
        return $this->name_label . " - " . $this->description;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'guard_name']);
    }
}
