<?php

namespace App\Models\Voucher\VoucherActivityLog;

use App\Models\Voucher\VoucherActivity;

class VoucherActivityLog
{
    private VoucherActivity $activity;

    public function __construct(VoucherActivity $activity)
    {
        $this->activity = $activity;
    }

    private function fromToActive(string $from, string $description = ""): VoucherActivity
    {
        $this->activity->from_state = $from;
        $this->activity->to_state = VoucherActivity::STATE_ACTIVE;

        if (!empty($description)) $this->activity->description = $description;

        $this->activity->save();

        return $this->activity;
    }

    public function fromCreationToActive(string $description = ""): VoucherActivity
    {
        return $this->fromToActive(VoucherActivity::STATE_CREATED, $description);
    }

    public function fromActive(): ToResolvedState
    {
        $this->activity->from_state = VoucherActivity::STATE_ACTIVE;

        return new ToResolvedState($this->activity);
    }

    public function fromFrozenToActive(string $description = ""): VoucherActivity
    {
        return $this->fromToActive(VoucherActivity::STATE_FROZEN, $description);
    }
}
