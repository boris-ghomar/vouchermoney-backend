<?php

namespace App\Services\Voucher\Contracts;

use App\Models\Voucher\VoucherCode;

interface VoucherCodeServiceContract
{
    public function generate(): string;

    public function exists(string $code): bool;

    public function create(string $code): VoucherCode;
}
