<?php

namespace App\Nova\Metrics;

use App\Models\Customer;
use Carbon\Carbon;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;

class AccountBalance extends Value
{
    public $icon = "";

    /**
     * Calculate the value of the metric.
     *
     * @param  NovaRequest  $request
     * @return mixed
     */
    public function calculate(NovaRequest $request): ValueResult
    {
        if ($request->resource === "customers") {
            $customerId = $request->resourceId;
            $balance = Customer::find($customerId)->balance;
        } else {
            $balance = $request->user()?->customer->balance;
        }

        return $this->result($balance)->currency()->allowZeroResult()->format("0,0.00");
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
     * @return Carbon
     */
    public function cacheFor(): Carbon
    {
         return now()->addMinutes(5);
    }

    /**
     * Get the URI key for the metric.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'balance';
    }
}
