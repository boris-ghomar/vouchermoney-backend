<?php

namespace App\Models\Voucher;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Customer;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property  string        $id
 * @property  string        $code
 * @property  float         $amount
 * @property  string        $state
 * @property  array         $customer_data
 * @property  array         $recipient_data
 * @property  string|null   $note
 * @property  Carbon        $resolved_at
 * @property  Carbon|null   $created_at
 * @property  Carbon|null   $updated_at
 *
 * @property  Customer      $customer
 * @property  Customer      $recipient
 * @property  bool          $is_redeemed
 * @property  bool          $is_expired
 * @property  User|null     $resolver
 *
 * @property  Collection<VoucherActivity>  $activities
 *
 * @method  Builder|static  onlyRedeemed()
 * @method  Builder|static  onlyExpired()
 */
class ArchivedVoucher extends Model
{
    protected $table = "archived_vouchers";
    protected $primaryKey = "code";
    protected $keyType = "string";
    public $incrementing = false;
    public $timestamps = false;

    const STATE_REDEEMED = "redeemed";
    const STATE_EXPIRED = "expired";

    protected $fillable = [
        "code",
        "amount",
        "state",
        "resolved_at",
        "customer_data",
        "recipient_data",
        "note",
    ];

    protected $casts = [
        "state" => "boolean",
        "resolved_at" => "datetime",
        "customer_data" => "array",
        "recipient_data" => "array",
        "created_at" => "datetime",
        "updated_at" => "datetime"
    ];

    public function getResolverAttribute(): User|null
    {
        /** @var VoucherActivity $activity */
        $activity = $this->activities()->latest();

        return $activity->user;
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

    public function getCustomerAttribute(): Customer|null
    {
        return empty($this->customer_data["id"]) ? null : Customer::find($this->customer_data["id"]);
    }

    public function getRecipientAttribute(): Customer|null
    {
        return empty($this->recipient_data["id"]) ? null : Customer::find($this->recipient_data["id"]);
    }

    public static function makeRedeemed(Voucher $voucher, string $note = "", Customer $recipient = null): static
    {
        return static::make($voucher, true, $note, $recipient);
    }

    public static function makeExpired(Voucher $voucher, string $note = "", Customer $recipient = null): static
    {
        return static::make($voucher, false, $note, $recipient);
    }

    private static function make(Voucher $voucher, bool $state, string $note = "", Customer $recipient = null): static
    {
        $customer = $voucher->customer;

        $archivedVoucher = new static();
        $archivedVoucher->code = $voucher->code;
        $archivedVoucher->amount = $voucher->amount;
        $archivedVoucher->state = $state;
        $archivedVoucher->customer_data = $voucher->customer;
        $archivedVoucher->created_at = $voucher->created_at;
        $archivedVoucher->updated_at = $voucher->updated_at;

        if (!empty($recipient) && $recipient->id !== $customer->id) $archivedVoucher->recipient_data = $recipient;
        if (!empty($note)) $archivedVoucher->note = $note;

        $archivedVoucher->save();

        return $archivedVoucher;
    }

    public function activities(): HasMany
    {
        return $this->hasMany(VoucherActivity::class, "code", "code");
    }

    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, "model");
    }
}
