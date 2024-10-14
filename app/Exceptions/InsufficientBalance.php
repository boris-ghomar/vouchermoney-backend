<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalance extends Exception
{
    public function __construct(string $message = 'Insufficient balance for the transaction.')
    {
        parent::__construct($message);
    }
}
