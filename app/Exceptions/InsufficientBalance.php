<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalance extends Exception
{
    public function __construct()
    {
        parent::__construct("Insufficient balance for that action");
    }
}
