<?php

namespace App\Services;

use App\Models\Voucher;

class VoucherService
{
    public function generate(): string
    {
        // Define characters to use (exclude 0, O, I, 1, and l for readability)
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        // Generate a voucher code using the allowed characters
        $voucherCode = '';
        for ($i = 0; $i < 4; $i++) {
            $voucherCode .= substr(str_shuffle($characters), 0, 4);

            // Add hyphen between groups
            if ($i < 3) $voucherCode .= '-';
        }

        // Ensure uniqueness by checking the database
        while (Voucher::where('code', $voucherCode)->exists()) {
            // Re-generate the code if a duplicate is found
            $voucherCode = $this->generate();
        }

        return $voucherCode;
    }

    public function create()
    {

    }

    public function cancel()
    {

    }

    public function block()
    {

    }

    public function unblock()
    {

    }
}
