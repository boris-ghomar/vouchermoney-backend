<?php

namespace App\Services\Finance;

use App\Models\Customer;
use App\Models\Finance\ArchivedFinance;
use App\Models\Finance\Finance;
use App\Services\Customer\Contracts\CustomerServiceContract;
use App\Services\Finance\Contracts\FinanceServiceContract;
use App\Services\Notification\Contracts\NotificationServiceContract;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinanceService implements FinanceServiceContract
{
    public function __construct(
        protected CustomerServiceContract $customerService,
        protected NotificationServiceContract $notificationService
    ) {}

    public function makeWithdraw(Customer $customer, float $amount, string|null $requester_comment = null): Finance
    {
        return $this->make($customer, abs($amount) * -1, $requester_comment);
    }

    public function makeDeposit(Customer $customer, float $amount, string|null $requester_comment = null): Finance
    {
        return $this->make($customer, abs($amount), $requester_comment);
    }

    protected function make(Customer $customer, float $amount, string|null $requester_comment = null): Finance
    {
        $finance = new Finance();
        $finance->customer()->associate($customer);
        $finance->amount = $amount;
        $finance->requester_id = auth()->user()->getAuthIdentifier();

        if (! empty($requester_comment)) $finance->requester_comment = $requester_comment;

        $finance->save();

        return $finance;
    }

    public function approve(Finance $finance, string|null $resolver_comment = null): ArchivedFinance
    {
        return $this->archive($finance, ArchivedFinance::STATUS_APPROVED, $resolver_comment);
    }

    public function reject(Finance $finance, string|null $resolver_comment = null): ArchivedFinance
    {
        return $this->archive($finance, ArchivedFinance::STATUS_REJECTED, $resolver_comment);
    }

    protected function archive(Finance $finance, bool $status, string|null $resolver_comment = null): ArchivedFinance
    {
        return DB::transaction(function () use ($finance, $status, $resolver_comment) {
            $archived = $this->makeArchived($finance, $status, $resolver_comment);

            $finance->transaction?->transactionable()->associate($archived);

            if ($status === $finance->is_deposit) {
                $description = ($status ? "Deposit" : "Withdraw") .  " finance request " . ($status ? "approved" : "rejected") . "!";
                $this->customerService->deposit($finance->customer, $finance->amount, $description, $archived);
            }

            $this->notificationService->financeHasBeenResolved($archived);

            $finance->delete();

            return $archived;
        });
    }

    public function requestWithdraw(Customer $customer, float $amount, string|null $requester_comment = null): Finance
    {
        return DB::transaction(function () use ($customer, $amount, $requester_comment) {
            // Create Finance request model.
            $finance = $this->makeWithdraw($customer, $amount, $requester_comment);

            // Create withdrawal Transaction for Customer.
            $this->customerService->withdraw($customer, $amount, "Make withdrawal finance request", $finance);

            // Send notification about finance requesting.
            $this->notificationService->financeHasBeenRequested($finance);

            return $finance;
        });
    }

    public function requestDeposit(Customer $customer, float $amount, string|null $requester_comment = null): Finance
    {
        return DB::transaction(function () use ($customer, $amount, $requester_comment) {
            // Create Finance request model.
            $finance = $this->makeDeposit($customer, $amount, $requester_comment);

            // Send notification about finance requesting.
            $this->notificationService->financeHasBeenRequested($finance);

            return $finance;
        });
    }

    public function cancelRequests(Collection|Finance|EloquentCollection $finances): void
    {
        DB::transaction(function () use ($finances) {
            if (! is_iterable($finances)) {
                $finances = [$finances];
            }

            $depositAmount = 0.0;
            $count = 0;

            /** @var Finance $finance */
            foreach ($finances as $finance) {
                if ($finance->is_withdraw) {
                    $depositAmount += abs($finance->amount);
                }

                $count++;
            }

            $customer = $finance->customer;

            if (! empty($depositAmount) && $depositAmount > 0) {
                $this->customerService->deposit($customer, $depositAmount, "Withdraw finance request" . ($count === 1 ? "" : "s") . " cancelled");
            }

            $this->delete($finances);

            $this->notificationService->financeHasBeenCanceled($customer->admin);
        });
    }

    protected function makeArchived(Finance $finance, bool $status, string|null $resolver_comment = null): ArchivedFinance
    {
        $archived = new ArchivedFinance();
        $archived->id = $finance->id;
        $archived->amount = $finance->amount;
        $archived->status = $status;
        $archived->customer_id = $finance->customer_id;
        $archived->requester_id = $finance->requester_id;
        $archived->resolver_id = auth()->user()->getAuthIdentifier();

        if (! empty($finance->requester_comment)) $archived->requester_comment = $finance->requester_comment;
        if (! empty($resolver_comment)) $archived->resolver_comment = $resolver_comment;

        $archived->created_at = $finance->created_at;
        $archived->updated_at = $finance->updated_at;
        $archived->save();

        return $archived;
    }

    public function delete(EloquentCollection|Finance|Collection $finances): void
    {
        if (! is_iterable($finances)) $finances = [$finances];

        $ids = [];

        foreach ($finances as $finance) $ids[] = $finance->id;

        Finance::destroy($ids);
    }
}
