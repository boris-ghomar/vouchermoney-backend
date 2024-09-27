<?php

namespace App\Nova\Metrics;

use App\Models\Customer;
use App\Models\User;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class AccountBalance extends Value
{
    public $icon = "credit-card";

    public $name = "Balance";

    /**
     * Calculate the value of the metric.
     *
     * @param  NovaRequest  $request
     * @return ValueResult
     */
    public function calculate(NovaRequest $request): ValueResult
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->is_admin) {
            $customerId = $request->resourceId;
            $customer = Customer::find($customerId);
        } else {
            $customer = $user->customer;
        }

        return $this->result($customer->balance)->currency()->allowZeroResult()->format("0,0.00");
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
     *
     * @return null
     */
    public function cacheFor(): null
    {
         return null;
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'customer-balance';
    }
}
