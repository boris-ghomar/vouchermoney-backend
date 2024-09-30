<?php

namespace App\Models\Voucher;

use App\Exceptions\AttemptToRedeemFrozenVoucher;
use App\Exceptions\InsufficientBalance;
use App\Exceptions\TransactionWithZeroAmount;
use App\Exceptions\VoucherArchivingFailed;
use App\Models\Customer;
use App\Models\Voucher\VoucherActivityLog\VoucherActivityLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @property  string       $code
 * @property  string       $customer_id
 * @property  float        $amount
 * @property  bool         $active
 * @property  Carbon|null  $created_at
 * @property  Carbon|null  $updated_at
 *
 * @property  Customer|null $customer
 *
 * @method  Builder|static  onlyActive()
 * @method  Builder|static  onlyFrozen()
 */
class Voucher extends Model
{
    protected $primaryKey = "code";
    protected $keyType = "string";
    public $incrementing = false;

    protected $fillable = [
        "code",
        "customer_id",
        "active"
    ];

    protected $casts = [
        "active" => "boolean"
    ];

    const STATE_ACTIVE = true;
    const STATE_FROZEN = false;

    public static function generate(Customer $customer, float $amount): static
    {
        return DB::transaction(function () use ($customer, $amount) {
            $customer->withdraw($amount, "Generating voucher");

            $voucher = new static();
            $voucher->code = VoucherCode::generate();
            $voucher->amount = $amount;
            $voucher->customer_id = $customer->id;
            $voucher->save();

            $voucher->makeLog()->fromCreationToActive();

            return $voucher;
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function freeze(): static
    {
        DB::transaction(function () {
            $this->active = static::STATE_FROZEN;
            $this->save();

            $this->makeLog()->fromActive()->toFrozen();
        });

        return $this;
    }

    public function activate(): static
    {
        DB::transaction(function () {
            $this->active = static::STATE_ACTIVE;
            $this->save();

            $this->makeLog()->fromFrozenToActive();
        });

        return $this;
    }

    /**
     * Make voucher redeemed
     *
     * @throws AttemptToRedeemFrozenVoucher
     */
    public function redeem(Customer $recipient = null): ArchivedVoucher
    {
        if (!$this->active)
            throw new AttemptToRedeemFrozenVoucher($this->customer, $this, auth()->user(), $recipient);

        return DB::transaction(function () use ($recipient) {
            $customer = !empty($recipient) ? $recipient : $this->customer;

            $customer->deposit($this->amount, "Redeem voucher [" . $this->code . "]");

            return $this->archive(ArchivedVoucher::STATE_REDEEMED, $recipient);
        });
    }

    /**
     * Make voucher expired
     */
    public function expire(): ArchivedVoucher
    {
        return DB::transaction(function () {
            $this->customer->deposit($this->amount, "Voucher [" . $this->code . "] has expired");

            return $this->archive(ArchivedVoucher::STATE_EXPIRED);
        });
    }

    /**
     * Archive voucher. Delete it from `vouchers` table and create in `archived_vouchers` table
     *
     * @param bool $state true if 'redeemed' or false if 'expired'
     * @param Customer|null $recipient
     * @return ArchivedVoucher
     * @throws VoucherArchivingFailed
     */
    private function archive(bool $state, Customer $recipient = null): ArchivedVoucher
    {
        $archivedVoucher = ArchivedVoucher::make($this, $state, $recipient);

        $this->makeLog()->fromActive()->to($state, "Archiving voucher");

        $this->delete();

        return $archivedVoucher;
    }

    private function makeLog(): VoucherActivityLog
    {
        return VoucherActivity::log($this->code, auth()->user());
    }

    public function scopeOnlyActive(Builder $query): void
    {
        $query->where("active", self::STATE_ACTIVE);
    }

    public function scopeOnlyFrozen(Builder $query): void
    {
        $query->where("active", self::STATE_FROZEN);
    }
}
