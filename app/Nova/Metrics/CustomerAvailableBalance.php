<?php

namespace App\Nova\Metrics;

use App\Models\Customer\Customer;
use App\Models\User;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class CustomerAvailableBalance extends Value
{
    public $name = "Available balance";
    public $icon = "credit-card";

    /**
     * Calculate the value of the metric.
     *
     * @param  NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request): ValueResult
    {
        /** @var User $user */
        $user = $request->user();

        if ($user?->is_admin) {
            $customerId = $request->resourceId;
            /** @var Customer $customer */
            $customer = Customer::find($customerId);
        } else {
            $customer = $user?->customer;
        }

        return $this->result(!$customer ? 0 : $customer->available_balance)->currency()->allowZeroResult()->format("0,0.00");
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array
     */
    public function ranges(): array
    {
        return [];
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     */
    public function cacheFor(): null
    {
        return null;
    }
}
