<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\DB;

class Transaction extends AbstractTransaction
{
    use HasUlids;

    public function archive(): ArchivedTransaction
    {
        return DB::transaction(function () {
            $this->customer->balance += $this->amount;
            $this->customer->save();

            $archivedTransaction = ArchivedTransaction::make($this);

            $this->delete();

            return $archivedTransaction;
        });
    }
}
