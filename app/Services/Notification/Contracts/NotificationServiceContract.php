<?php

namespace App\Services\Notification\Contracts;

use App\Models\Finance\ArchivedFinance;
use App\Models\Finance\Finance;
use App\Models\Voucher\ArchivedVoucher;
use App\Models\Voucher\Voucher;

interface NotificationServiceContract
{
    public function financeHasBeenRequested(Finance $finance): void;

    public function financeHasBeenCanceled(Finance $finance): void;

    public function financeHasBeenResolved(ArchivedFinance $finance): void;

    public function voucherHasBeenGenerated(Voucher $voucher): void;

    public function voucherHasBeenRedeemed(ArchivedVoucher $voucher): void;
}
