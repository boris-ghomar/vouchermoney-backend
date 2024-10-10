<?php

namespace App\Services\Notification;

use App\Models\Finance\ArchivedFinance;
use App\Models\Finance\Finance;
use App\Models\Permission;
use App\Models\User;
use App\Models\Voucher\ArchivedVoucher;
use App\Models\Voucher\Voucher as VoucherModel;
use App\Nova\Resources\Finance\Finance as FinanceResource;
use App\Nova\Resources\Voucher\Voucher;
use App\Services\Notification\Contracts\NotificationServiceContract;

class NotificationService implements NotificationServiceContract
{
    public function financeHasBeenRequested(Finance $finance): void
    {
        $notification = NovaNotification::info("Finance request has been sent!");
        $notification->action("/resources/active-finances/" . $finance->id);
        $notification->icon(FinanceResource::ICON);
        $notification->send($finance->customer->admin);
    }

    public function financeHasBeenCanceled(mixed $notifiable): void
    {
        $notification = NovaNotification::error("Finance request has been canceled!");
        $notification->icon(FinanceResource::ICON);
        $notification->send($notifiable);
    }

    public function financeHasBeenResolved(ArchivedFinance $finance): void
    {
        $method = $finance->is_approved ? NovaNotification::TYPE_SUCCESS : NovaNotification::TYPE_ERROR;

        $users = $finance->customer->users->filter(function (User $user) {
            return $user->can(Permission::CUSTOMER_FINANCE) || $user->is_customer_admin;
        });

        $notification = NovaNotification::{$method}("Finance request " . ($finance->is_approved ? "approved" : "rejected") . "!");
        $notification->action("/resources/archived-finances/" . $finance->id);
        $notification->icon(FinanceResource::ICON);
        $notification->send($users);
    }

    public function voucherHasBeenGenerated(VoucherModel|iterable $vouchers): void
    {
        $description = (is_iterable($vouchers) ? "Batch of vouchers have" : "Voucher has") . " been generated!";
        $action = "/resources/active-vouchers" . (is_iterable($vouchers) ? "" : "/" . $vouchers->id);

        /** @var VoucherModel $voucher */
        $send = (is_iterable($vouchers) ? $vouchers[0] : $vouchers)->customer->admin;

        $notification = NovaNotification::info($description);
        $notification->action($action);
        $notification->icon(Voucher::ICON);
        $notification->send($send);
    }

    public function voucherHasBeenRedeemed(ArchivedVoucher $voucher): void
    {
        $notification = NovaNotification::info("Voucher [$voucher->code] redeemed!");
        $notification->action("/resources/archived-vouchers/$voucher->id");
        $notification->icon(Voucher::ICON);
        $notification->send($voucher->recipient_id ? $voucher->recipient->admin : $voucher->customer->admin);
    }

    public function voucherHasBeenExpired(ArchivedVoucher $voucher): void
    {
        $notification = NovaNotification::info("Voucher [$voucher->code] has been expired!");
        $notification->action("/resources/archived-vouchers/$voucher->id");
        $notification->icon(Voucher::ICON);
        $notification->send($voucher->customer->admin);
    }
}
