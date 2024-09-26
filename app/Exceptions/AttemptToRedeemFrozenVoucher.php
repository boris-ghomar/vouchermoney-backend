<?php

namespace App\Exceptions;

use App\Models\Customer;
use App\Models\User;
use App\Models\Voucher\Voucher;
use Exception;

class AttemptToRedeemFrozenVoucher extends Exception
{
    public function __construct(Customer $customer, Voucher $voucher, User $user = null, Customer $recipient = null)
    {
        $message = "Attempt to redeem frozen voucher [" . $voucher->code . "]";

        activity(static::class)
            ->withProperties([
                "customer" => $customer,
                "recipient" => $recipient,
                "voucher" => $voucher,
                "user" => $user,
            ])
            ->performedOn($voucher)
            ->causedBy($user)
            ->log($message);

        parent::__construct($message);
    }
}
