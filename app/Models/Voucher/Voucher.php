<?php

namespace App\Models\Voucher;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

/**
 * @property  bool  $active
 *
 * @property-read  bool  $is_active
 * @property-read  bool  $is_frozen
 *
 * @method  Builder|static  onlyActive()
 * @method  Builder|static  onlyFrozen()
 * @method  static  Builder|static  onlyActive()
 * @method  static  Builder|static  onlyFrozen()
 */
class Voucher extends AbstractVoucher
{
    use HasUlids;

    const STATE_ACTIVE = true;
    const STATE_FROZEN = false;

    protected array $additional_fillable = ["active"];
    protected array $additional_casts = ["active" => "boolean"];
    protected array $additional_log_columns = ["active"];

    public function scopeOnlyActive(Builder $query): void
    {
        $query->where("active", self::STATE_ACTIVE);
    }

    public function scopeOnlyFrozen(Builder $query): void
    {
        $query->where("active", self::STATE_FROZEN);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->active === static::STATE_ACTIVE;
    }

    public function getIsFrozenAttribute(): bool
    {
        return $this->active === static::STATE_FROZEN;
    }
}
