<?php

namespace App\Services\Finance\Contracts;

use App\Models\Customer\Customer;
use App\Models\Finance\Finance;

interface FinanceServiceContract
{
    /**
     * Create a new finance request for a withdrawal on behalf of the specified customer.
     *
     * This method creates a new instance of the Finance model to represent a request
     * for the customer to withdraw an amount from their balance. It does not perform
     * any transactions; it merely prepares the request for further processing by
     * the application's administration.
     *
     * @param Customer $customer The customer for whom the withdrawal request is being created.
     * @param float $amount The amount to be withdrawn.
     * @param string $comment A comment or reason associated with the withdrawal request.
     *
     * @return Finance The newly created Finance request instance representing the withdrawal.
     */
    public function makeWithdraw(Customer $customer, float $amount, string $comment): Finance;

    /**
     * Create a new finance request for a deposit on behalf of the specified customer.
     *
     * This method creates a new instance of the Finance model to represent a request
     * for the customer to deposit an amount into their balance. It does not perform
     * any transactions; it merely prepares the request for further processing by
     * the application's administration.
     *
     * @param Customer $customer The customer for whom the deposit request is being created.
     * @param float $amount The amount to be deposited.
     * @param string $comment A comment or reason associated with the deposit request.
     *
     * @return Finance The newly created Finance request instance representing the deposit.
     */
    public function makeDeposit(Customer $customer, float $amount, string $comment): Finance;

    /**
     * Delete the specified finance request.
     *
     * This method removes the provided Finance request instance from the system.
     * It does not perform any additional checks or business logic; it simply deletes
     * the request from the database.
     *
     * @param Finance $finance The finance request to be deleted.
     *
     * @return void
     */
    public function delete(Finance $finance): void;
}
