<?php

namespace App\Services\Notification;

use App\Models\Customer\Customer;
use App\Models\Finance\ArchivedFinance;
use App\Models\Finance\Finance;
use App\Models\Permission;
use App\Models\User;
use App\Nova\Resources\Finance\Finance as FinanceResource;

class NotificationService
{
    public static function sendFinanceRequestedNotification(Customer $customer, Finance $finance): void
    {
        $notification = NovaNotification::info("Finance request has been sent!");
        $notification->action("/resources/finances/" . $finance->id);
        $notification->icon(FinanceResource::ICON);
        $notification->send($customer->admin);
    }

    public static function sendFinanceResolvedNotification(Customer $customer, ArchivedFinance $finance): void
    {
        $method = $finance->is_approved ? NovaNotification::TYPE_SUCCESS : NovaNotification::TYPE_ERROR;

        $users = $customer->users->filter(function (User $user) {
            return $user->can(Permission::CUSTOMER_FINANCE) || $user->is_customer_admin;
        });

        $notification = NovaNotification::{$method}("Finance request has been sent!");
        $notification->action("/resources/archived-finances/" . $finance->id);
        $notification->icon(FinanceResource::ICON);
        $notification->send($users);
    }
}
