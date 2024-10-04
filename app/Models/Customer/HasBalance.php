<?php

namespace App\Models\Customer;

/**
 * @property-read  float  $available_balance
 */
trait HasBalance
{
    public function getAvailableBalanceAttribute(): float
    {
        return $this->calculateBalance();
    }

    /**
     * Check if customer's balance more or equal positive given amount
     */
    public function hasEnoughBalance(float $amount): bool
    {
        $amount = abs($amount);

        return $this->calculateBalance() >= $amount;
    }

    /**
     * Calculate available balance
     */
    private function calculateBalance(): float
    {
        $balance = $this->balance;

        foreach ($this->transactions()->get() as $transaction) {
            $balance += $transaction->amount;
        }

        return $balance;
    }
}
