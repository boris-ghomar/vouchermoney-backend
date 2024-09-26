<?php

namespace App\Models\Voucher;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property  string        $code
 * @property  float         $amount
 * @property  string        $state
 * @property  array         $customer_data
 * @property  array         $recipient_data
 * @property  Carbon        $resolved_at
 * @property  Carbon|null   $created_at
 * @property  Carbon|null   $updated_at
 *
 * @property  Customer      $customer
 * @property  Customer      $recipient
 *
 * @method  Builder|static  redeemed()
 * @method  Builder|static  expired()
 */
class ArchivedVoucher extends Model
{
    protected $table = "archived_vouchers";
    protected $primaryKey = "code";
    protected $keyType = "string";
    public $incrementing = false;

    const STATE_REDEEMED = "redeemed";
    const STATE_EXPIRED = "expired";

    protected $fillable = [
        "code",
        "amount",
        "state",
        "resolved_at"
    ];

    protected $casts = [
        "resolved_at" => "datetime",
        "customer_data" => "array",
        "recipient_data" => "array",
    ];

    public function scopeRedeemed(Builder $query): void
    {
        $query->where("state", self::STATE_REDEEMED);
    }

    public function scopeExpired(Builder $query): void
    {
        $query->where("state", self::STATE_EXPIRED);
    }

    public function getCustomerAttribute(): Customer | null
    {
        if (empty($this->customer_data["id"]))
            return null;

        return Customer::find($this->customer_data["id"]);
    }

    public function getRecipientAttribute(): Customer | null
    {
        if (empty($this->recipient_data["id"]))
            return null;

        return Customer::find($this->recipient_data["id"]);
    }
}
