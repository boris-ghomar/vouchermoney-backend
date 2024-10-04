<?php

namespace App\Models\Finance;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read  string  $type "withdraw" or "deposit"
 * @property-read  bool    $is_deposit
 * @property-read  bool    $is_withdraw
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

    public function scopeOnlyWithdraws(Builder $query): void
    {
        $query->where("amount", "<", 0);
    }

    public function scopeOnlyDeposits(Builder $query): void
    {
        $query->where("amount", ">", 0);
    }
}
