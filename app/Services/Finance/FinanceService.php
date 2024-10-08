<?php

namespace App\Services\Finance;

use App\Models\Customer\Customer;
use App\Models\Finance\Finance;
use App\Services\Finance\Contracts\FinanceServiceContract;

class FinanceService implements FinanceServiceContract
{
    public function makeWithdraw(Customer $customer, float $amount, string $comment): Finance
    {
        return $this->make($customer, abs($amount) * -1, $comment);
    }

    public function makeDeposit(Customer $customer, float $amount, string $comment): Finance
    {
        return $this->make($customer, abs($amount), $comment);
    }

    protected function make(Customer $customer, float $amount, string $comment = ""): Finance
    {
        $finance = new Finance();
        $finance->customer()->associate($customer);
        $finance->amount = $amount;

        $requester = auth()->user();
        if (! empty($requester)) $finance->requester()->associate($requester);

        if (! empty($comment)) $finance->comment = $comment;

        $finance->save();

        return $finance;
    }

    public function delete(Finance $finance): void
    {
        $finance->delete();
    }
}
