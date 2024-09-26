<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use App\Models\Voucher\Voucher;
use Illuminate\Support\Facades\DB;

class VoucherService
{
    public function create(User $user, Customer $customer, float $amount): Voucher
    {
        $voucher = new Voucher();

        DB::transaction(function () use ($customer, $amount, $voucher) {

            $customer->transact($amount, "Voucher generation");
        });

        return $voucher;
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
