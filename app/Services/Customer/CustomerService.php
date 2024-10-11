<?php

namespace App\Services\Customer;

use App\Exceptions\AttemptToCreateExpiredApiToken;
use App\Exceptions\AttemptToRedeemFrozenVoucher;
use App\Exceptions\InsufficientBalance;
use App\Exceptions\TransactionWithZeroAmount;
use App\Exceptions\WithdrawalLimitExceeded;
use App\Models\Customer;
use App\Models\CustomerApiToken;
use App\Models\Transaction\Transaction;
use App\Models\Voucher\ArchivedVoucher;
use App\Models\Voucher\Voucher;
use App\Services\Customer\Contracts\CustomerServiceContract;
use App\Services\Notification\Contracts\NotificationServiceContract;
use App\Services\Transaction\Contracts\TransactionServiceContract;
use App\Services\User\Contracts\UserServiceContract;
use App\Services\Voucher\Contracts\VoucherServiceContract;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerService implements CustomerServiceContract
{
    public function __construct(
        protected TransactionServiceContract $transactionService,
        protected UserServiceContract $userService,
        protected NotificationServiceContract $notificationService,
        protected VoucherServiceContract $voucherService
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

    public function generateVoucher(Customer $customer, float $amount, int $count = 1): Voucher|Collection
    {
        return DB::transaction(function () use ($customer, $amount, $count) {
            $this->canWithdrawOrFail($customer, abs($amount) * $count);

            $vouchersCollection = collect();

            for($i = 0; $i < $count; $i++) {
                $voucher = $this->voucherService->generate($customer, $amount);

                $vouchersCollection->push($voucher);

                $this->withdraw($customer, $amount, "Generate voucher", $voucher);
            }

            if ($vouchersCollection->count() === 1) {
                $vouchersCollection = $vouchersCollection->first();
            }

            $this->notificationService->voucherHasBeenGenerated($vouchersCollection);

            return $vouchersCollection;
        });
    }

    public function redeemVoucher(Customer $customer, Voucher $voucher, string $note = ""): ArchivedVoucher
    {
        if ($voucher->is_frozen)
            throw new AttemptToRedeemFrozenVoucher();

        return DB::transaction(function () use ($customer, $voucher, $note) {
            $archived = $this->voucherService->redeem($voucher, $customer, $note);

            $voucher->transaction->transactionable()->associate($archived);

            $this->deposit($customer, $voucher->amount, "Redeem voucher [$archived->code]", $archived);

            $this->notificationService->voucherHasBeenRedeemed($archived);

            $this->voucherService->delete($voucher);

            return $archived;
        });
    }

    public function expireVoucher(Voucher $voucher): ArchivedVoucher
    {
        return DB::transaction(function () use ($voucher) {
            $archived = $this->voucherService->expire($voucher);

            $voucher->transaction->transactionable()->associate($archived);

            $this->deposit($voucher->customer, $voucher->amount, "Voucher [$archived->code] has been expired", $archived);

            $this->notificationService->voucherHasBeenExpired($archived);

            $this->voucherService->delete($voucher);

            return $archived;
        });
    }

    public function allCustomersPlucked(): array
    {
        return Customer::pluck("name", "id")->toArray();
    }

    // TODO:
    public function delete(Customer $customer): void {}

    public function createApiToken(Customer $customer, string $name, array $permissions, Carbon $expires_at = null): string
    {
        $token_string = CustomerApiToken::createTokenString();

        $token = new CustomerApiToken();
        $token->customer()->associate($customer);
        $token->token = CustomerApiToken::hash($token_string);
        $token->name = $name;

        if (! empty($expires_at)) {
            if ($expires_at->lte(now())) {
                throw new AttemptToCreateExpiredApiToken();
            }

            $token->expires_at = $expires_at;
        }

        $token->save();

        $token->syncPermissions($permissions);

        return $token_string;
    }
}
