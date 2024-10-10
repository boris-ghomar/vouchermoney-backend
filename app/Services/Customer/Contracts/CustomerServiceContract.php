<?php

namespace App\Services\Customer\Contracts;

use App\Exceptions\AttemptToRedeemFrozenVoucher;
use App\Exceptions\InsufficientBalance;
use App\Exceptions\TransactionWithZeroAmount;
use App\Exceptions\WithdrawalLimitExceeded;
use App\Models\Customer;
use App\Models\Finance\Finance;
use App\Models\Transaction\Transaction;
use App\Models\Voucher\ArchivedVoucher;
use App\Models\Voucher\Voucher;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

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
     * Get customer plucked key => value as "id" => "name"
     *
     * @return array Customers plucked
     */
    public function allCustomersPlucked(): array;

    /**
     * Generates one or multiple vouchers for the specified customer.
     * This method creates a new voucher or multiple vouchers with the given amount
     * and associates them with the provided customer. It returns a single voucher if
     * only one is created, or a collection of vouchers if multiple are generated.
     *
     * The process typically includes:
     * - Validating the customer's eligibility for generating a voucher.
     * - Creating one or more voucher records with the specified amount.
     * - Associating the vouchers with the customer.
     * - Returning the generated voucher(s) as a single instance or a collection.
     *
     * @param Customer $customer The customer for whom the voucher is being generated.
     * @param float $amount The amount assigned to each voucher.
     * @param int $count The number of vouchers to be generated (default is 1).
     * @return Voucher|Collection A single voucher if one is generated, or a collection of vouchers if multiple are generated.
     */
    public function generateVoucher(Customer $customer, float $amount, int $count = 1): Voucher|Collection;

    /**
     * Redeems the specified voucher for the given customer, performing all necessary business logic.
     * This method handles the complete process of voucher redemption, including:
     * - Validating the customer's eligibility to redeem the voucher.
     * - Updating the voucher's status to indicate that it has been redeemed.
     * - Adjusting the balance of the customer's account based on the voucher's value.
     * - Creating and returning an archived version of the voucher to record the redemption.
     * - Recording any provided notes related to the redemption process.
     *
     * @param Customer $customer The customer redeeming the voucher.
     * @param Voucher $voucher The voucher to be redeemed.
     * @param string $note Optional note for the redemption process (default is an empty string).
     * @return ArchivedVoucher The archived version of the voucher after it has been redeemed.
     *
     * @throws AttemptToRedeemFrozenVoucher If the voucher is frozen and cannot be redeemed.
     */
    public function redeemVoucher(Customer $customer, Voucher $voucher, string $note = ""): ArchivedVoucher;

    /**
     * Expires the specified voucher, performing all necessary business logic.
     * This method marks the voucher as expired and creates an archived version
     * to record the expiration. It ensures that the voucher is no longer usable
     * by updating its status and preserving a record of the expiration.
     *
     * The process typically includes:
     * - Validating the current status of the voucher to ensure it can be expired.
     * - Updating the voucher's status to indicate expiration.
     * - Creating and returning an `ArchivedVoucher` instance to keep a record
     *   of the expired voucher.
     *
     * @param Voucher $voucher The voucher to be expired.
     * @return ArchivedVoucher The archived version of the voucher after it has been expired.
     */
    public function expireVoucher(Voucher $voucher): ArchivedVoucher;

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
