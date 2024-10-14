<?php

namespace App\Models\Traits;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read  Customer  $customer
 */
trait HasCustomer
{
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
