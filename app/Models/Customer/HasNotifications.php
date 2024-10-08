<?php

namespace App\Models\Customer;

use App\Models\Finance\ArchivedFinance;
use App\Models\Finance\Finance;
use App\Models\Voucher\ArchivedVoucher;
use App\Models\Voucher\Voucher;
use App\Services\Notification\NotificationService;

trait HasNotifications
{
    public function sendFinanceRequestedNotification(Finance $finance): void
    {
        NotificationService::sendFinanceRequestedNotification($this, $finance);
    }

    public function sendFinanceResolvedNotification(ArchivedFinance $finance): void
    {
        NotificationService::sendFinanceResolvedNotification($this, $finance);
    }

    public function sendVoucherGeneratedNotification(Voucher $voucher): void
    {
        NotificationService::sendVoucherGeneratedNotification($this, $voucher);
    }

    public function sendVoucherRedeemedNotification(ArchivedVoucher $voucher): void
    {
        NotificationService::sendVoucherRedeemedNotification($this, $voucher);
    }

    public function sendRedeemVoucherNotification(ArchivedVoucher $voucher): void
    {
        NotificationService::sendRedeemVoucherNotification($this, $voucher);
    }
}
