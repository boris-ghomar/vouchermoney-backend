<?php

namespace App\Models\Customer;


use App\Models\Voucher\Voucher;

trait HasVouchers
{
    public function generateVoucher(float $amount): Voucher
    {
        return Voucher::generate($this, $amount);
    }
}
