<?php

namespace App\Exceptions;

use Exception;

class TransactionWithZeroAmount extends Exception
{
    public function __construct(string $message = "Transaction amount cannot be zero.")
    {
        parent::__construct($message);
    }
}
