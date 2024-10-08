<?php

namespace App\Services\Customer;

use App\Exceptions\InsufficientBalance;
use App\Exceptions\TransactionWithZeroAmount;
use App\Exceptions\WithdrawalLimitExceeded;
use App\Models\Customer\Customer;
use App\Models\Finance\Finance;
use App\Models\Transaction\Transaction;
use App\Services\Customer\Contracts\CustomerServiceContract;
use App\Services\Finance\Contracts\FinanceServiceContract;
use App\Services\Notification\Contracts\NotificationServiceContract;
use App\Services\Transaction\Contracts\TransactionServiceContract;
use App\Services\User\Contracts\UserServiceContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomerService implements CustomerServiceContract
{
    public function __construct(
        protected TransactionServiceContract $transactionService,
        protected UserServiceContract $userService,
        protected FinanceServiceContract $financeService,
        protected NotificationServiceContract $notificationService
    ) {}

    public function computeBalance(Customer $customer): float
    {
        $balance = $customer->balance;

        foreach ($customer->transactions()->get() as $transaction) {
            $balance += $transaction->amount;
        }

        return $balance;
    }

    public function exceedsWithdrawalLimit(Customer $customer, float $amount): bool
    {
        return false;
    }

    public function canWithdrawOrFail(Customer $customer, float $amount): void
    {
        $amount = abs($amount);

        $this->ensureAmountIsValid($amount);

        if ($this->computeBalance($customer) < $amount)
            throw new InsufficientBalance();

        if ($this->exceedsWithdrawalLimit($customer, $amount))
            throw new WithdrawalLimitExceeded();
    }

    public function ensureAmountIsValid(float $amount): void
    {
        if (empty($amount)) throw new TransactionWithZeroAmount();
    }

    public function withdraw(Customer $customer, float $amount, string $description, Model $associated = null): Transaction
    {
        $this->canWithdrawOrFail($customer, $amount);

        return $this->transactionService->withdraw($customer, $amount, $description, $associated);
    }

    public function deposit(Customer $customer, float $amount, string $description, Model $associated = null): Transaction
    {
        $this->ensureAmountIsValid($amount);

        return $this->transactionService->deposit($customer, $amount, $description, $associated);
    }

    public function makeMerchant(string $name, string $email, string $password): Customer
    {
        return $this->make(Customer::TYPE_MERCHANT, $name, $email, $password);
    }

    public function makeReseller(string $name, string $email, string $password): Customer
    {
        return $this->make(Customer::TYPE_RESELLER, $name, $email, $password);
    }

    private function make(string $type, string $name, string $email, string $password): Customer
    {
        return DB::transaction(function () use ($name, $type, $email, $password) {
            $customer = new Customer();
            $customer->name = $name;
            $customer->type = $type;
            $customer->save();

            $this->userService->createForCustomer($customer, "Admin", $email, $password);

            return $customer;
        });
    }

    public function requestWithdraw(Customer $customer, float $amount, string $comment): Finance
    {
        return DB::transaction(function () use ($customer, $amount, $comment) {
            // Create Finance request model.
            $finance = $this->financeService->makeWithdraw($customer, $amount, $comment);

            // Create withdrawal Transaction for Customer.
            $this->withdraw($customer, $amount, "Make withdrawal finance request", $finance);

            // Send notification about finance requesting.
            $this->notificationService->financeHasBeenRequested($finance);

            return $finance;
        });
    }

    public function requestDeposit(Customer $customer, float $amount, string $comment): Finance
    {
        return DB::transaction(function () use ($customer, $amount, $comment) {
            // Create Finance request model.
            $finance = $this->financeService->makeDeposit($customer, $amount, $comment);

            // Send notification about finance requesting.
            $this->notificationService->financeHasBeenRequested($finance);

            return $finance;
        });
    }

    public function cancelRequest(Finance $finance): void
    {
        DB::transaction(function () use ($finance) {
            if ($finance->is_withdraw)
                $this->deposit($finance->customer, $finance->amount, "Withdraw finance request cancelled");

            $this->financeService->delete($finance);

            $this->notificationService->financeHasBeenCanceled($finance);
        });
    }

    // TODO:
    public function delete(Customer $customer): void {}
}
