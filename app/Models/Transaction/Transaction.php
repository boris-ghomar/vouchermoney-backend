<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\DB;

class Transaction extends AbstractTransaction
{
    use HasUlids;
}
