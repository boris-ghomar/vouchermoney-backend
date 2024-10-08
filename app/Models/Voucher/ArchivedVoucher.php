<?php

namespace App\Models\Voucher;

use App\Models\Customer\Customer;
use App\Models\Transaction\ArchivedTransaction;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property  string       $id
 * @property  string       $code
 * @property  float        $amount
 * @property  string       $state
 * @property  array        $customer_data
 * @property  array        $creator_data
 * @property  array|null   $recipient_data
 * @property  string|null  $recipient_note
 * @property  Carbon       $resolved_at
 * @property  Carbon       $created_at
 * @property  Carbon       $updated_at
 *
 * @property  Customer       $customer
 * @property  User           $creator
 * @property  Customer|null  $recipient
 * @property  bool           $is_redeemed
 * @property  bool           $is_expired
 * @property  User|null      $resolver
 *
 * @property  Collection<VoucherActivity>  $activities
 *
 * @method  Builder|static  onlyRedeemed()
 * @method  Builder|static  onlyExpired()
 */
class ArchivedVoucher extends Model
{
    use LogsActivity;

    protected $table = "archived_vouchers";
    protected $keyType = "string";
    public $incrementing = false;
    public $timestamps = false;

    const STATE_REDEEMED = "redeemed";
    const STATE_EXPIRED = "expired";

    protected $fillable = [
        "code",
        "amount",
        "state",

        "customer_data",
        "creator_data",
        "recipient_data",

        "recipient_note",

        "resolved_at",
        "created_at",
        "updated_at"
    ];

    protected $casts = [
        "state" => "boolean",
        "amount" => "decimal:2",

        "customer_data" => "array",
        "creator_data" => "array",
        "recipient_data" => "array",

        "resolved_at" => "datetime",
        "created_at" => "datetime",
        "updated_at" => "datetime"
    ];

    public function getResolverAttribute(): User|null
    {
        /** @var VoucherActivity $activity */
        $activity = $this->activities()->latest();

        return $activity->user;
    }

    public function getCustomerAttribute(): Customer|null
    {
        return empty($this->customer_data["id"]) ? null : Customer::find($this->customer_data["id"]);
    }

    public function getRecipientAttribute(): Customer|null
    {
        return empty($this->recipient_data["id"]) ? null : Customer::find($this->recipient_data["id"]);
    }

    public function getCreatorAttribute(): User|null
    {
        return empty($this->creator_data["id"]) ? null : User::find($this->creator_data["id"]);
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

    public static function makeRedeemed(Voucher $voucher, string $note = "", Customer $recipient = null): static
    {
        return static::make($voucher, static::STATE_REDEEMED, $note, $recipient);
    }

    public static function makeExpired(Voucher $voucher, string $note = "", Customer $recipient = null): static
    {
        return static::make($voucher, static::STATE_EXPIRED, $note, $recipient);
    }

    private static function make(Voucher $voucher, string $state, string $note = "", Customer $recipient = null): static
    {
        $customer = $voucher->customer;

        $archivedVoucher = new static();
        $archivedVoucher->id = $voucher->id;
        $archivedVoucher->code = $voucher->code;
        $archivedVoucher->amount = $voucher->amount;
        $archivedVoucher->state = $state;

        $archivedVoucher->customer_data = $voucher->customer;
        $archivedVoucher->creator_data = $voucher->creator;

        if (! empty($recipient) && $recipient->id !== $customer->id) $archivedVoucher->recipient_data = $recipient;
        if (! empty($note)) $archivedVoucher->recipient_note = $note;

        $archivedVoucher->created_at = $voucher->created_at;
        $archivedVoucher->updated_at = $voucher->updated_at;

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

    public function archived_transaction(): MorphOne
    {
        return $this->morphOne(ArchivedTransaction::class, "model");
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'code',
                'amount',
                'state',
                'customer_data',
                'requester_data',
                'recipient_data',
                'recipient_note',
                'resolved_at',
                'created_at',
                'updated_at'
            ]);
    }
}
