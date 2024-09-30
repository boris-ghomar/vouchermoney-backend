<?php

namespace App\Models\Voucher;

use App\Exceptions\VoucherArchivingFailed;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property  string        $code
 * @property  float         $amount
 * @property  boolean       $state
 * @property  array         $customer_data
 * @property  array         $recipient_data
 * @property  Carbon        $resolved_at
 * @property  Carbon|null   $created_at
 * @property  Carbon|null   $updated_at
 *
 * @property  Customer      $customer
 * @property  Customer      $recipient
 * @property  bool          $is_redeemed
 * @property  bool          $is_expired
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

    const STATE_REDEEMED = true;
    const STATE_EXPIRED = false;

    protected $fillable = [
        "code",
        "amount",
        "state",
        "resolved_at",
        "customer_data",
        "recipient_data",
    ];

    protected $casts = [
        "state" => "boolean",
        "resolved_at" => "datetime",
        "customer_data" => "array",
        "recipient_data" => "array",
    ];

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

    /**
     * @param  bool  $state True if 'redeemed' or false if 'expired'
     * @throws VoucherArchivingFailed
     */
    public static function make(Voucher $voucher, bool $state, Customer $recipient = null): static
    {
        $customer = $voucher->customer;

        if (empty($customer))
            throw new VoucherArchivingFailed($voucher);

        $archivedVoucher = new static();
        $archivedVoucher->code = $voucher->code;
        $archivedVoucher->amount = $voucher->amount;
        $archivedVoucher->state = $state;
        $archivedVoucher->customer_data = $voucher->customer->toJson();
        $archivedVoucher->created_at = $voucher->created_at;
        $archivedVoucher->updated_at = $voucher->updated_at;

        if (!empty($recipient)) $archivedVoucher->recipient_data = $recipient->toJson();

        $archivedVoucher->save();

        return $archivedVoucher;
    }
}
