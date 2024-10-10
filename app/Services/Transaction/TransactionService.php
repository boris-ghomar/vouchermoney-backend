<?php

namespace App\Services\Transaction;

use App\Models\Customer;
use App\Models\Transaction\ArchivedTransaction;
use App\Models\Transaction\Transaction;
use App\Services\Customer\Contracts\CustomerServiceContract;
use App\Services\Transaction\Contracts\TransactionServiceContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TransactionService implements TransactionServiceContract
{
    public function withdraw(Customer $customer, float $amount, string $description = "", Model $associated = null): Transaction
    {
        return $this->make($customer, abs($amount) * -1, $description, $associated);
    }

    public function deposit(Customer $customer, float $amount, string $description = "", Model $associated = null): Transaction
    {
        return $this->make($customer, abs($amount), $description, $associated);
    }

    protected function make(Customer $customer, float $amount, string $description = "", Model $associated = null): Transaction
    {
        $transaction = new Transaction();
        $transaction->customer()->associate($customer);
        $transaction->amount = $amount;

        if (! empty($description)) $transaction->description = $description;
        if (! empty($associated)) $transaction->transactionable()->associate($associated);

        $transaction->save();

        return $transaction;
    }

    public function archive(Transaction $transaction): ArchivedTransaction
    {
        return DB::transaction(function () use ($transaction) {
            $customer = $transaction->customer;

            $archived = new ArchivedTransaction();
            $archived->id = $transaction->id;
            $archived->customer()->associate($customer);
            $archived->amount = $transaction->amount;
            $archived->description = $transaction->description;

            if (! empty($transaction->transactionable)) $archived->transactionable()->associate($transaction->transactionable);

            $archived->created_at = $transaction->created_at;
            $archived->updated_at = $transaction->updated_at;
            $archived->save();

            $customer->balance += $transaction->amount;
            $customer->save();

            $transaction->delete();

            return $archived;
        });
    }
}
