<?php

namespace App\Services\Voucher;

use App\Models\Voucher\VoucherCode;
use App\Services\Voucher\Contracts\VoucherCodeServiceContract;

class VoucherCodeService implements VoucherCodeServiceContract
{
    public function generate(): string
    {
        // XXXX
        $group_length = 4;
        // XXXX - XXXX - XXXX - XXXX - XXXX - XXXX
        $groups = 6;
        // Exclude 0, O, I, 1, and l for readability
        $available_chars = "ABCDEFGHJKLMNPQRSTUVWXYZ23456789";

        $code = '';

        for ($i = 0; $i < $groups; $i++) {
            $code .= substr(str_shuffle($available_chars), 0, $group_length);

            // Add hyphen between groups
            if ($i < $groups - 1) $code .= '-';
        }

        // Ensure uniqueness by checking the database
        while ($this->exists($code)) {
            // Re-generate the code if a duplicate is found
            $code = static::generate();
        }

        // Add code to database
        $this->create($code);

        return $code;
    }

    public function exists(string $code): bool
    {
        return VoucherCode::query()->where("code", $code)->exists();
    }

    public function create(string $code): VoucherCode
    {
        $voucherCode = new VoucherCode();
        $voucherCode->code = $code;
        $voucherCode->save();

        return $voucherCode;
    }
}
