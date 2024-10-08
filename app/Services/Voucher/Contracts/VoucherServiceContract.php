<?php

namespace App\Services\Voucher\Contracts;

use App\Models\Customer\Customer;
use App\Models\Voucher\Voucher;

interface VoucherServiceContract
{
    public function generate(Customer $customer, float $amount): Voucher;
}
