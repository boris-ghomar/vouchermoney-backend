<?php

namespace App\Services\Notification\Contracts;

use App\Models\Finance\ArchivedFinance;
use App\Models\Finance\Finance;
use App\Models\Voucher\ArchivedVoucher;
use App\Models\Voucher\Voucher;
use Illuminate\Support\Collection;

interface NotificationServiceContract
{
    public function financeHasBeenRequested(Finance $finance): void;

    public function financeHasBeenCanceled($notifiable): void;

    public function financeHasBeenResolved(ArchivedFinance $finance): void;

    public function voucherHasBeenGenerated(Voucher|Collection $vouchers): void;

    public function voucherHasBeenRedeemed(ArchivedVoucher $voucher): void;

    public function voucherHasBeenExpired(ArchivedVoucher $voucher): void;
}
