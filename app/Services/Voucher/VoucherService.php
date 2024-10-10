<?php

namespace App\Services\Voucher;

use App\Models\Customer;
use App\Models\User;
use App\Models\Voucher\ArchivedVoucher;
use App\Models\Voucher\Voucher;
use App\Services\Voucher\Contracts\VoucherCodeServiceContract;
use App\Services\Voucher\Contracts\VoucherServiceContract;

class VoucherService implements VoucherServiceContract
{
    public function __construct(
        protected VoucherCodeServiceContract $voucherCodeService,
    ) {}

    public function generate(Customer $customer, float $amount): Voucher
    {
        $voucher = new Voucher();
        $voucher->code = $this->voucherCodeService->generate();
        $voucher->amount = abs($amount);
        $voucher->customer()->associate($customer);
        $voucher->creator()->associate(auth()->user());
        $voucher->save();

        return $voucher;
    }

    public function freeze(Voucher $voucher): Voucher
    {
        $voucher->active = Voucher::STATE_FROZEN;
        $voucher->save();

        return $voucher;
    }

    public function activate(Voucher $voucher): Voucher
    {
        $voucher->active = Voucher::STATE_ACTIVE;
        $voucher->save();

        return $voucher;
    }

    public function redeem(Voucher $voucher, Customer $recipient = null, string $note = ""): ArchivedVoucher
    {
        return $this->archive($voucher, ArchivedVoucher::STATE_REDEEMED, $recipient, $note);
    }

    public function expire(Voucher $voucher): ArchivedVoucher
    {
        return $this->archive($voucher, ArchivedVoucher::STATE_EXPIRED);
    }

    private function archive(Voucher $voucher, string $state, Customer $recipient = null, string $note = ""): ArchivedVoucher
    {
        $archived = new ArchivedVoucher();
        $archived->id = $voucher->id;
        $archived->code = $voucher->code;
        $archived->amount = $voucher->amount;
        $archived->state = $state;
        $archived->customer_id = $voucher->customer_id;
        $archived->creator_id = $voucher->creator_id;
        $archived->creator_type = $voucher->creator_type;

        if (! empty($note)) {
            $archived->note = $note;
        }

        if (! empty($recipient) && $recipient->id !== $voucher->customer_id) {
            $archived->recipient()->associate($recipient);
        }

        if ($state === ArchivedVoucher::STATE_EXPIRED) {
            $resolver = User::administrator();
        } else {
            $resolver = auth()->user();
        }

        $archived->resolver()->associate($resolver);
        $archived->created_at = $voucher->created_at;
        $archived->updated_at = $voucher->updated_at;
        $archived->save();

        return $archived;
    }

    public function delete(Voucher $voucher): void
    {
        $voucher->delete();
    }
}
