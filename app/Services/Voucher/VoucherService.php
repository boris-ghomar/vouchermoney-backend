<?php

namespace App\Services\Voucher;

use App\Models\Customer\Customer;
use App\Models\Voucher\Voucher;
use App\Models\Voucher\VoucherActivity;
use App\Services\Voucher\Contracts\VoucherCodeServiceContract;
use App\Services\Voucher\Contracts\VoucherServiceContract;
use Illuminate\Support\Facades\DB;

class VoucherService implements VoucherServiceContract
{
    public function __construct(protected VoucherCodeServiceContract $voucherCodeService) {}

    public function generate(Customer $customer, float $amount, int $count = 1): Voucher
    {
        return DB::transaction(function () use ($customer, $amount, $count) {
            // Create Voucher
            $voucher = $this->make($customer, $amount, auth()->user());

            // Discount amount from customer's balance via transaction
            $customer->withdraw($amount, "Generate voucher [$voucher->code]", $voucher);

            // Send notification to customer, that voucher's generated
            $customer->sendVoucherGeneratedNotification($voucher);

            VoucherActivity::makeCreated($voucher->code);

            return $voucher;
        });
    }

    protected function make(Customer $customer, float $amount, $creator = null): Voucher
    {
        $voucher = new Voucher();
        $voucher->code = $this->voucherCodeService->generate();
        $voucher->amount = abs($amount);
        $voucher->customer()->associate($customer);

        if (! empty($creator)) $voucher->creator()->associate($creator);

        $voucher->save();

        return $voucher;
    }
}
