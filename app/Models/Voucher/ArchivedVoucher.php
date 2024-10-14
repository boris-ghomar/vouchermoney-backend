<?php

namespace App\Models\Voucher;

use App\Models\AbstractUser;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property  string       $state
 * @property  string|null  $recipient_id
 * @property  string|null  $note
 * @property  string       $resolver_type
 * @property  string       $resolver_id
 * @property  Carbon       $resolved_at
 *
 * @property-read  Customer|null  $recipient
 * @property-read  AbstractUser   $resolver
 * @property-read  bool           $is_redeemed
 * @property-read  bool           $is_expired
 *
 * @method  Builder|static  onlyRedeemed()
 * @method  Builder|static  onlyExpired()
 * @method  static  Builder|static  onlyRedeemed()
 * @method  static  Builder|static  onlyExpired()
 */
class ArchivedVoucher extends AbstractVoucher
{
    const STATE_REDEEMED = "redeemed";
    const STATE_EXPIRED = "expired";

    protected $table = "archived_vouchers";
    public $timestamps = false;

    protected array $additional_fillable = [
        "state",
        "recipient_id",
        "resolver_type",
        "resolver_id",
        "note",
        "resolved_at",
    ];

    protected array $additional_casts = [
        "resolved_at" => "datetime",
    ];

    protected array $additional_log_columns = [
        "state",
        "recipient_id",
        "resolver_type",
        "resolver_id",
        "note",
        "resolved_at",
    ];

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Customer::class, "recipient_id");
    }

    public function resolver(): MorphTo
    {
        return $this->morphTo("resolver");
    }

    public function getIsRedeemedAttribute(): bool
    {
        return $this->state === static::STATE_REDEEMED;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->state === static::STATE_EXPIRED;
    }

    public function scopeOnlyRedeemed(Builder $query): void
    {
        $query->where("state", self::STATE_REDEEMED);
    }

    public function scopeOnlyExpired(Builder $query): void
    {
        $query->where("state", self::STATE_EXPIRED);
    }
}
