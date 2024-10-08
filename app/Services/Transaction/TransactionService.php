<?php

namespace App\Services\Transaction;

use App\Models\Customer\Customer;
use App\Models\Transaction\Transaction;
use App\Services\Transaction\Contracts\TransactionServiceContract;
use Illuminate\Database\Eloquent\Model;

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
        if (! empty($associated)) $transaction->model()->associate($associated);

        $transaction->save();

        return $transaction;
    }
}
