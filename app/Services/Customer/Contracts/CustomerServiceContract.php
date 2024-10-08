<?php

namespace App\Services\Customer\Contracts;

use App\Exceptions\InsufficientBalance;
use App\Exceptions\TransactionWithZeroAmount;
use App\Exceptions\WithdrawalLimitExceeded;
use App\Models\Customer\Customer;
use App\Models\Finance\Finance;
use App\Models\Transaction\Transaction;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface CustomerServiceContract
{
    /**
     * Calculates the current balance for the specified customer.
     * This may include various factors such as transactions, pending amounts, and adjustments.
     *
     * @param Customer $customer The customer whose balance is to be computed.
     *
     * @return float The computed balance for the customer.
     */
    public function computeBalance(Customer $customer): float;

    /**
     * Check if the specified withdrawal amount exceeds the customer's withdrawal limit.
     *
     * @param Customer $customer The customer to check against.
     * @param float $amount The withdrawal amount to check.
     *
     * @return bool True if the amount exceeds the withdrawal limit; otherwise, false.
     */
    public function exceedsWithdrawalLimit(Customer $customer, float $amount): bool;

    /**
     * Checks if the customer has sufficient balance and is within the allowed withdrawal limit
     * to perform a withdrawal. Throws an exception if either condition is not met.
     *
     * @param Customer $customer The customer requesting the withdrawal.
     * @param float $amount The amount to be withdrawn.
     *
     * @throws InsufficientBalance If the customer's balance is insufficient for the withdrawal.
     * @throws WithdrawalLimitExceeded If the withdrawal amount exceeds the allowed limit for the customer.
     * @throws TransactionWithZeroAmount If the amount is empty (eg., 0 or 0.0).
     */
    public function canWithdrawOrFail(Customer $customer, float $amount): void;

    /**
     * Check the validity of the given amount.
     *
     * This method checks if the provided amount is not empty.
     *
     * @param float $amount The amount to be validated.
     *
     * @throws TransactionWithZeroAmount If the amount is zero.
     */
    public function ensureAmountIsValid(float $amount): void;

    /**
     * Withdraw the specified amount from the customer's balance via transaction.
     *
     * @param Customer $customer The customer requesting the withdrawal.
     * @param float $amount The amount to be withdrawn.
     * @param string $description A description for the withdrawal transaction.
     * @param Model|null $associated An optional associated model related to the transaction (e.g., a voucher or finance).
     *
     * @return Transaction The transaction record created for the withdrawal.
     *
     * @throws InsufficientBalance If the customer's balance is insufficient for the withdrawal.
     * @throws WithdrawalLimitExceeded If the withdrawal exceeds the allowed limit for the customer.
     * @throws TransactionWithZeroAmount If the amount is empty (eg., 0 or 0.0).
     */
    public function withdraw(Customer $customer, float $amount, string $description, Model $associated = null): Transaction;

    /**
     * Deposit the specified amount to the customer's balance via transaction.
     *
     * @param Customer $customer The customer to deposit the amount to.
     * @param float $amount The amount to be deposited.
     * @param string $description A description for the transaction.
     * @param Model|null $associated An optional associated model for additional context (e.g., a payment method).
     *
     * @return Transaction The created transaction record.
     *
     * @throws TransactionWithZeroAmount If the amount is empty (eg., 0 or 0.0).
     */
    public function deposit(Customer $customer, float $amount, string $description, Model $associated = null): Transaction;

    /**
     * Create a new merchant customer.
     *
     * This method registers a new customer with merchant privileges by accepting
     * the required details such as name, email, and password.
     * The newly created merchant customer will have specific capabilities and access
     * related to merchant operations.
     *
     * @param string $name The name of the merchant.
     * @param string $email The email address of the merchant.
     * @param string $password The password for the merchant account.
     *
     * @return Customer The newly created merchant customer instance.
     */
    public function makeMerchant(string $name, string $email, string $password): Customer;

    /**
     * Create a new reseller customer.
     *
     * This method registers a new customer with reseller privileges by accepting
     * the required details such as name, email, and password.
     * The newly created reseller customer will have specific capabilities and access
     * related to reseller operations.
     *
     * @param string $name The name of the reseller.
     * @param string $email The email address of the reseller.
     * @param string $password The password for the reseller account.
     *
     * @return Customer The newly created reseller customer instance.
     */
    public function makeReseller(string $name, string $email, string $password): Customer;

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
     * @param Finance $finance The finance request to be canceled and deleted.
     *
     * @return void
     */
    public function cancelRequest(Finance $finance): void;

    /**
     * Delete the specified customer.
     *
     * This method removes the customer from the system along with any associated data.
     * Ensure that this operation is handled with caution, as it is irreversible.
     *
     * @param Customer $customer The customer instance to be deleted.
     *
     * @return void
     *
     * @throws ModelNotFoundException If the customer does not exist.
     * @throws Exception If there are issues during the deletion process.
     */
    public function delete(Customer $customer): void;
}
