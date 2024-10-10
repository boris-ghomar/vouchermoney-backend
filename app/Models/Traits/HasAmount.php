<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * @property-read  string  $type "withdraw" or "deposit"
 * @property-read  bool    $is_deposit
 * @property-read  bool    $is_withdraw
 *
 * @method  static  Builder|static  onlyWithdraws()
 * @method  static  Builder|static  onlyDeposits()
 * @method  Builder|static          onlyWithdraws()
 * @method  Builder|static          onlyDeposits()
 */
trait HasAmount
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

    public function scopeOnlyWithdraws(Builder $query): void
    {
        $query->where("amount", "<", 0);
    }

    public function scopeOnlyDeposits(Builder $query): void
    {
        $query->where("amount", ">", 0);
    }
}
