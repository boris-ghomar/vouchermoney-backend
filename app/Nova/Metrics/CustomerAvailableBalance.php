<?php

namespace App\Nova\Metrics;

use App\Models\Customer;
use App\Models\Permission;
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

        if (empty($user) || ! $user->canAny([Permission::CUSTOMERS_VIEW, Permission::CUSTOMER_VIEW])) {
            return $this->result(0);
        }

        if ($user->is_admin && $request->resourceId) {
            $customerId = $request->resourceId;
            $customer = Customer::find($customerId);
        } else {
            $customer = $user->customer;
        }

        return $this->result($customer->available_balance)->currency()->allowZeroResult()->format("0,0.00");
    }

    public function cacheFor(): null
    {
        return null;
    }
}
