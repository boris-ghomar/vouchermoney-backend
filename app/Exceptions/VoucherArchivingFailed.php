<?php

namespace App\Exceptions;

use App\Models\User;
use App\Models\Voucher\Voucher;
use Exception;

class VoucherArchivingFailed extends Exception
{
    public function __construct(Voucher $voucher, User $user = null)
    {
        $message = "Archiving voucher [". $voucher->code . "] failed";

        activity(static::class)
            ->withProperties([
                "voucher" => $voucher,
                "user" => $user
            ])
            ->performedOn($voucher)
            ->causedBy($user)->log($message);

        parent::__construct($message);
    }
}
