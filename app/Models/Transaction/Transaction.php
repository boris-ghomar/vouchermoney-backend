<?php

namespace App\Models\Transaction;

use App\Exceptions\AttemptToArchiveTransactionWithoutCustomer;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\DB;

class Transaction extends AbstractTransaction
{
    use HasUlids;

    /**
     * @throws AttemptToArchiveTransactionWithoutCustomer
     */
    public function archive(): ArchivedTransaction
    {
        $customer = $this->customer;

        if (empty($customer))
            throw new AttemptToArchiveTransactionWithoutCustomer();

        return DB::transaction(function () use ($customer) {
            $customer->balance += $this->amount;
            $customer->save();

            $archivedTransaction = ArchivedTransaction::make($this);

            $this->delete();

            return $archivedTransaction;
        });
    }
}
