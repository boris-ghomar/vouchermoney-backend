<?php

namespace App\Services\Notification;

use App\Models\Customer\Customer;
use App\Models\Finance\ArchivedFinance;
use App\Models\Finance\Finance;
use App\Models\Permission;
use App\Models\User;
use App\Models\Voucher\ArchivedVoucher;
use App\Models\Voucher\Voucher;
use App\Nova\Resources\Finance\Finance as FinanceResource;
use App\Nova\Resources\Voucher\ActiveVoucher;

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

        $notification = NovaNotification::{$method}("Finance request " . ($finance->is_approved ? "approved" : "rejected") . "!");
        $notification->action("/resources/archived-finances/" . $finance->id);
        $notification->icon(FinanceResource::ICON);
        $notification->send($users);
    }

    public static function sendVoucherGeneratedNotification(Customer $customer, Voucher $voucher): void
    {
        $notification = NovaNotification::info("Voucher [$voucher->code] has been created");
        $notification->action("/resources/active-vouchers/$voucher->id");
        $notification->icon(ActiveVoucher::ICON);
        $notification->send($customer->admin);
    }

    public static function sendVoucherRedeemedNotification(Customer $customer, ArchivedVoucher $voucher): void
    {
        $notification = NovaNotification::info("Voucher [$voucher->code] redeemed");
        $notification->action("/resources/archived-vouchers/$voucher->id");
        $notification->icon(ActiveVoucher::ICON);
        $notification->send($customer->admin);
    }

    public static function sendRedeemVoucherNotification(Customer $customer, ArchivedVoucher $voucher): void
    {
        $notification = NovaNotification::success("Redeem [$voucher->code] voucher");
        $notification->action("/resources/archived-vouchers/$voucher->id");
        $notification->icon(ActiveVoucher::ICON);
        $notification->send($customer->admin);
    }
}