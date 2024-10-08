<?php

namespace App\Services\Transaction\Contracts;

use App\Models\Customer\Customer;
use App\Models\Transaction\Transaction;
use Illuminate\Database\Eloquent\Model;

interface TransactionServiceContract
{
    /**
     * Create a new transaction record for a withdrawal from the customer's balance.
     *
     * This method is responsible for instantiating a Transaction object
     * representing the withdrawal. It does not perform all operations
     * typically associated with a withdrawal (e.g., checking balance,
     * deducting amount from the customer's balance). The actual
     * withdrawal operations should be handled separately.
     *
     * @param Customer $customer The customer from whose account the amount will be withdrawn.
     * @param float $amount The amount to be withdrawn.
     * @param string $description An optional description for the transaction (default is an empty string).
     * @param Model|null $associated An optional associated model for additional context (e.g., voucher or finance).
     *
     * @return Transaction The created transaction record representing the withdrawal.
     */
    public function withdraw(Customer $customer, float $amount, string $description = "", Model $associated = null): Transaction;

    /**
     * Create a new transaction record for a deposit to the customer's balance.
     *
     * This method is responsible for instantiating a Transaction object
     * representing the deposit. It does not perform all operations
     * typically associated with a deposit (e.g., updating the customer's
     * balance). The actual deposit operations should be handled separately.
     *
     * @param Customer $customer The customer to whose account the amount will be deposited.
     * @param float $amount The amount to be deposited.
     * @param string $description An optional description for the transaction (default is an empty string).
     * @param Model|null $associated An optional associated model for additional context (e.g., voucher or finance).
     *
     * @return Transaction The created transaction record representing the deposit.
     */
    public function deposit(Customer $customer, float $amount, string $description = "", Model $associated = null): Transaction;
}
