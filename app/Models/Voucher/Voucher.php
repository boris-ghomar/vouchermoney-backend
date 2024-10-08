<?php

namespace App\Models\Voucher;

use App\Exceptions\AttemptToRedeemFrozenVoucher;
use App\Models\Customer\Customer;
use App\Models\Transaction\AbstractTransaction;
use App\Models\Transaction\ArchivedTransaction;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property  string       $id
 * @property  string       $code
 * @property  float        $amount
 * @property  bool         $active
 * @property  string       $customer_id
 * @property  string|null  $creator_id
 * @property  string|null  $creator_type
 * @property  Carbon       $created_at
 * @property  Carbon       $updated_at
 *
 * @property-read  User         $creator
 * @property-read  Customer     $customer
 * @property-read  bool         $is_active
 * @property-read  bool         $is_frozen
 *
 * @property-read  Transaction          $transaction
 * @property-read  ArchivedTransaction  $archived_transaction
 * @property-read  AbstractTransaction  $associated_transaction
 *
 * @method  Builder|static  onlyActive()
 * @method  Builder|static  onlyFrozen()
 */
class Voucher extends Model
{
    use HasUlids, LogsActivity;

    protected $fillable = [
        "code",
        "customer_id",
        "creator_id",
        "amount",
        "active"
    ];

    protected $casts = [
        "active" => "boolean",
        "amount" => "decimal:2"
    ];

    const STATE_ACTIVE = true;
    const STATE_FROZEN = false;
    const FREEZE_VOUCHER = 'freeze';
    const ACTIVATE_VOUCHER = 'activate';

    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, "model");
    }

    public function archived_transaction(): MorphOne
    {
        return $this->morphOne(ArchivedTransaction::class, "model");
    }

    public function getAssociatedTransaction(): AbstractTransaction
    {
        return $this->transaction ?: $this->archived_transaction;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): MorphTo
    {
        return $this->morphTo("creator");
    }

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

    public function freeze(): static
    {
        DB::transaction(function () {
            $this->active = static::STATE_FROZEN;
            $this->save();

            VoucherActivity::makeFrozen($this->code);
        });

        return $this;
    }

    public function activate(): static
    {
        DB::transaction(function () {
            $this->active = static::STATE_ACTIVE;
            $this->save();

            VoucherActivity::makeActivated($this->code);
        });

        return $this;
    }

    /**
     * @throws AttemptToRedeemFrozenVoucher
     */
    public function redeem(string $note = "", Customer $recipient = null)
    {
        if (! $this->active)
            throw new AttemptToRedeemFrozenVoucher();

        return DB::transaction(function () use ($recipient, $note) {
            $customer = !empty($recipient) ? $recipient : $this->customer;

            $customer->deposit($this->amount, "Voucher [$this->code] redeemed", $this);

            $archived = ArchivedVoucher::makeRedeemed($this, $note, $recipient);

            VoucherActivity::makeRedeemed($this->code);

            $this->associated_transaction?->model()->associate($archived);

            $this->delete();

            if (empty($recipient)) $this->customer->sendRedeemVoucherNotification($archived);
            else {
                $recipient->sendRedeemVoucherNotification($archived);
                $this->customer->sendVoucherRedeemedNotification($archived);
            }

            return $archived;
        });
    }

    public function expire(): ArchivedVoucher
    {
        return DB::transaction(function () {
            $archived = ArchivedVoucher::makeExpired($this);

            $this->customer->deposit($this->amount, "Voucher [" . $this->code . "] has been expired", $archived);

            VoucherActivity::makeExpired($this->code);

            $this->associated_transaction?->model()->associate($archived);

            $this->delete();

            return $archived;
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['code', 'amount', 'active', 'customer_id', 'created_at', 'updated_at']);
    }

    public static function generate(Customer $customer, float $amount): static
    {
        return DB::transaction(function () use ($customer, $amount) {
            $voucher = new static();
            $voucher->code = VoucherCode::generate();
            $voucher->amount = abs($amount);
            $voucher->customer_id = $customer->id;

            /** @var User $creator */
            $creator = auth()->user();

            if (! empty($creator)) $voucher->creator()->associate($creator);

            $voucher->save();

            $customer->withdraw($amount, "Generate voucher [$voucher->code]", $voucher);

            $customer->sendVoucherGeneratedNotification($voucher);

            VoucherActivity::makeCreated($voucher->code);

            return $voucher;
        });
    }

    public static function findByCode(string $code): static|null
    {
        return static::query()->where("code", $code)->first();
    }
}
