<?php

namespace App\Models\Voucher\VoucherActivityLog;

use App\Models\Voucher\VoucherActivity;

class ToResolvedState
{
    private VoucherActivity $activity;

    public function __construct(VoucherActivity $activity)
    {
        $this->activity = $activity;
    }

    public function to(string $state, string $description = ""): VoucherActivity
    {
        $this->activity->to_state = $state;

        if (!empty($description)) $this->activity->description = $description;

        $this->activity->save();

        return $this->activity;
    }

    public function toFrozen(string $description = ""): VoucherActivity
    {
        return $this->to(VoucherActivity::STATE_FROZEN, $description);
    }

    public function toRedeemed(string $description = ""): VoucherActivity
    {
        return $this->to(VoucherActivity::STATE_REDEEMED, $description);
    }

    public function toExpired(string $description = ""): VoucherActivity
    {
        return $this->to(VoucherActivity::STATE_EXPIRED, $description);
    }
}
