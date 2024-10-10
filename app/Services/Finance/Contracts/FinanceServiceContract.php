<?php

namespace App\Services\Finance\Contracts;

use App\Models\Customer;
use App\Models\Finance\ArchivedFinance;
use App\Models\Finance\Finance;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

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
     * @param string $requester_comment A comment or reason associated with the withdrawal request.
     *
     * @return Finance The newly created Finance request instance representing the withdrawal.
     */
    public function makeWithdraw(Customer $customer, float $amount, string $requester_comment): Finance;

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
     * @param string $requester_comment A comment or reason associated with the deposit request.
     *
     * @return Finance The newly created Finance request instance representing the deposit.
     */
    public function makeDeposit(Customer $customer, float $amount, string $requester_comment): Finance;

    /**
     * Delete the specified finance request.
     *
     * This method removes the provided Finance request instance from the system.
     * It does not perform any additional checks or business logic; it simply deletes
     * the request from the database.
     *
     * @param EloquentCollection<Finance>|Collection<Finance>|Finance $finances The finance requests to be deleted.
     *
     * @return void
     */
    public function delete(EloquentCollection|Finance|Collection $finances): void;

    /**
     * Approves a finance request, allowing a customer to deposit or withdraw
     * funds to/from their balance. This method updates the finance request
     * status, adjusts the customer's balance accordingly, and performs any
     * necessary actions (e.g., logging, sending notifications).
     *
     * @param Finance $finance The finance request to be approved.
     * @param string $resolver_comment
     * @return ArchivedFinance
     */
    public function approve(Finance $finance, string $resolver_comment = ""): ArchivedFinance;

    /**
     * Request a withdrawal from the specified customer's balance.
     *
     * This method creates a new withdrawal request for the given customer and returns a Finance instance.
     * The request will be processed based on the customer's available balance and
     * the application's business rules.
     *
     * @param Customer $customer The customer initiating the withdrawal request.
     * @param float $amount The amount to withdraw.
     * @param string $comment A comment or reason for the withdrawal request.
     *
     * @return Finance The Finance instance representing the withdrawal request.
     */
    public function requestWithdraw(Customer $customer, float $amount, string $comment): Finance;

    /**
     * Request a deposit to the specified customer's balance.
     *
     * This method creates a new deposit request for the given customer and returns a Finance instance.
     * The deposit will be processed according to the application's business rules.
     *
     * @param Customer $customer The customer initiating the deposit request.
     * @param float $amount The amount to deposit.
     * @param string $comment A comment or reason for the deposit request.
     *
     * @return Finance The Finance instance representing the deposit request.
     */
    public function requestDeposit(Customer $customer, float $amount, string $comment): Finance;

    /**
     * Cancel and delete the specified finance request for the given customer.
     *
     * This method removes the provided Finance request instance from the system,
     * even if it is currently being processed. It ensures that the request
     * associated with the specified customer is properly canceled, and no further
     * action will be taken on it.
     *
     * @param Collection<Finance>|EloquentCollection<Finance>|Finance $finances The finance request to be canceled and deleted.
     *
     * @return void
     */
    public function cancelRequests(EloquentCollection|Finance|Collection $finances): void;

    /**
     * Rejects a finance request, preventing a customer from depositing or
     * withdrawing funds to/from their balance. This method updates the
     * finance request status to indicate the rejection and may perform
     * additional actions such as logging or sending a notification to the customer.
     *
     * @param Finance $finance The finance request to be rejected.
     * @param string $resolver_comment
     * @return ArchivedFinance
     */
    public function reject(Finance $finance, string $resolver_comment = ""): ArchivedFinance;
}
