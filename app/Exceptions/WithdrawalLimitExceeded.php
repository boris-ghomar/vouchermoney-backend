<?php

namespace App\Exceptions;

use Exception;

class WithdrawalLimitExceeded extends Exception
{
    public function __construct(string $message = 'Withdrawal limit exceeded.')
    {
        parent::__construct($message);
    }
}
