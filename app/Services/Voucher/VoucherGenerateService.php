<?php

namespace App\Services\Voucher;

use App\Exceptions\InsufficientBalance;
use App\Models\Customer\Customer;
use App\Models\Voucher\Voucher;
use App\Models\Voucher\VoucherActivity;
use App\Services\Customer\Contracts\CustomerServiceContract;
use App\Services\Voucher\Contracts\VoucherCodeServiceContract;
use App\Services\Voucher\Contracts\VoucherGenerateServiceContract;
use Illuminate\Support\Facades\DB;

class VoucherGenerateService implements VoucherGenerateServiceContract
{
    public function __construct(
        protected VoucherCodeServiceContract $voucherCodeService,
        protected CustomerServiceContract $customerService
    ) {}

    /**
     * @throws InsufficientBalance
     */
    public function generate(Customer $customer, float $amount, int $count = 1): Voucher
    {
        // Ensure the customer has sufficient balance for the withdrawal (calculated as count * amount).
        // If the balance is insufficient, throw an InsufficientBalanceException to prevent the transaction.
        $this->customerService->canWithdrawOrFail($customer, $count * $amount);

        $this->customerService

        return DB::transaction(function () use ($customer, $amount, $count) {

            // Create Voucher
            $voucher = $this->make($customer, $amount);

            // Discount amount from customer's balance via transaction
            $customer->withdraw($amount, "Generate voucher [$voucher->code]", $voucher);

            // Send notification to customer, that voucher's generated
            $customer->sendVoucherGeneratedNotification($voucher);

            VoucherActivity::makeCreated($voucher->code);

            return $voucher;
        });
    }

    protected function make(Customer $customer, float $amount): Voucher
    {
        $voucher = new Voucher();
        $voucher->code = $this->voucherCodeService->generate();
        $voucher->amount = abs($amount);
        $voucher->customer()->associate($customer);

        if (! empty(auth()->user())) $voucher->creator()->associate(auth()->user());

        $voucher->save();

        return $voucher;
    }
}
