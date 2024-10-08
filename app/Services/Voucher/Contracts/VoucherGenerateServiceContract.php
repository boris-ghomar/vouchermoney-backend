<?php

namespace App\Services\Voucher\Contracts;

use App\Models\Customer\Customer;
use App\Models\Voucher\Voucher;

interface VoucherGenerateServiceContract
{
    public function generate(Customer $customer, float $amount, int $count = 1): Voucher;
}
