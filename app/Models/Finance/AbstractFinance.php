<?php

namespace App\Models\Finance;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property  string  $id
 * @property  string  $customer_id
 * @property  float   $amount
 * @property  Carbon  $created_at
 * @property  Carbon  $updated_at
 *
 * @property-read  bool    $is_deposit
 * @property-read  bool    $is_withdraw
 * @property-read  string  $type "withdraw" or "deposit"
 *
 * @property-read  Customer  $customer
 *
 * @method  Builder|static  onlyWithdraws()
 * @method  Builder|static  onlyDeposits()
 */
abstract class AbstractFinance extends Model
{
    const TYPE_WITHDRAW = "withdraw";
    const TYPE_DEPOSIT = "deposit";

    public function getTypeAttribute(): string
    {
        return $this->amount < 0 ? static::TYPE_WITHDRAW : static::TYPE_DEPOSIT;
    }

    public function getIsDepositAttribute(): bool
    {
        return $this->amount > 0;
    }

    public function getIsWithdrawAttribute(): bool
    {
        return $this->amount < 0;
    }

    /**
     * Relationship to the customer
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo( Customer::class);
    }

    public function scopeOnlyWithdraws(Builder $query): void
    {
        $query->where("amount", "<", 0);
    }

    public function scopeOnlyDeposits(Builder $query): void
    {
        $query->where("amount", ">", 0);
    }
}
