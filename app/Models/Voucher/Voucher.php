<?php

namespace App\Models\Voucher;

use App\Exceptions\AttemptToRedeemFrozenVoucher;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @property  string       $id
 * @property  string       $code
 * @property  float        $amount
 * @property  bool         $active
 * @property  string       $customer_id
 * @property  Carbon       $created_at
 * @property  Carbon       $updated_at
 *
 * @property  Customer     $customer
 *
 * @method  Builder|static  onlyActive()
 * @method  Builder|static  onlyFrozen()
 */
class Voucher extends Model
{
    use HasUlids;

    protected $fillable = [
        "code",
        "customer_id",
        "amount",
        "active"
    ];

    protected $casts = [
        "active" => "boolean",
        "amount" => "decimal:2"
    ];

    const STATE_ACTIVE = true;
    const STATE_FROZEN = false;

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeOnlyActive(Builder $query): void
    {
        $query->where("active", self::STATE_ACTIVE);
    }

    public function scopeOnlyFrozen(Builder $query): void
    {
        $query->where("active", self::STATE_FROZEN);
    }

    public static function generate(Customer $customer, float $amount): static
    {
        return DB::transaction(function () use ($customer, $amount) {
            $customer->withdraw($amount, "Generating voucher");

            $customer->notifyAdministrator(
                "Voucher [$this->code] has been created",
                "/resources/vouchers/" . $this->code
            );

            $voucher = new static();
            $voucher->code = VoucherCode::generate();
            $voucher->amount = abs($amount);
            $voucher->customer_id = $customer->id;
            $voucher->save();

            VoucherActivity::makeCreated($voucher->code);

            return $voucher;
        });
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
     * @throws AttemptToRedeemFrozenVoucher
     */
    public function redeem(string $note = "", Customer $recipient = null)
    {
        if (! $this->active)
            throw new AttemptToRedeemFrozenVoucher();

        return DB::transaction(function () use ($recipient, $note) {
            $customer = !empty($recipient) ? $recipient : $this->customer;

            $customer->deposit($this->amount, $this->getVoucherRedeemedActivityDescription());

            return $this->archive(ArchivedVoucher::STATE_REDEEMED, $note, $recipient);
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
     */
    private function archive(bool $state, string $note = "", Customer $recipient = null): ArchivedVoucher
    {
        $archivedVoucher = ArchivedVoucher::make($this, $state, $note, $recipient);

        $this->makeLog()->fromActive()->to($state ? VoucherActivity::STATE_REDEEMED : VoucherActivity::STATE_EXPIRED, "Archiving voucher");

        $this->delete();

        return $archivedVoucher;
    }

    private function getVoucherRedeemedActivityDescription(): string
    {
        return "Voucher [$this->code] has been redeemed";
    }
}
