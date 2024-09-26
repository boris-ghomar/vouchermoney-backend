<?php

namespace App\Models\Voucher;

use App\Exceptions\AttemptToRedeemFrozenVoucher;
use App\Exceptions\InsufficientBalance;
use App\Exceptions\TransactionWithZeroAmount;
use App\Exceptions\VoucherArchivingFailed;
use App\Models\Customer;
use App\Models\Voucher\VoucherActivityLog\VoucherActivityLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @property  string $code
 * @property  int|null $customer_id
 * @property  float $amount
 * @property  boolean $active
 * @property  Carbon|null $created_at
 * @property  Carbon|null $updated_at
 *
 * @property  Customer|null $customer
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

    /**
     * @throws InsufficientBalance
     * @throws TransactionWithZeroAmount
     */
    public static function generate(Customer $customer, float $amount): static
    {
        $customer->withdraw($amount, "Generating voucher");

        $voucher = new static();
        $voucher->code = VoucherCode::generate();
        $voucher->amount = $amount;
        $voucher->customer_id = $customer->id;
        $voucher->save();

        $voucher->makeLog()->fromCreationToActive("Voucher [" . $voucher->code . "] - generated");

        return $voucher;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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
     * Archive voucher. Delete it from vouchers table and create in archived_vouchers table
     *
     * @param string $state Can be 'redeemed' or 'expired
     * @param Customer|null $recipient
     * @return ArchivedVoucher
     * @throws VoucherArchivingFailed
     */
    private function archive(string $state, Customer $recipient = null): ArchivedVoucher
    {
        $customer = $this->customer;

        if (empty($customer))
            throw new VoucherArchivingFailed($this);

        $archivedVoucher = new ArchivedVoucher();
        $archivedVoucher->code = $this->code;
        $archivedVoucher->amount = $this->amount;
        $archivedVoucher->state = $state;
        $archivedVoucher->customer_data = $this->customer->toJson();
        $archivedVoucher->created_at = $this->created_at;
        $archivedVoucher->updated_at = $this->updated_at;

        if (!empty($recipient)) $archivedVoucher->recipient_data = $recipient->toJson();

        $archivedVoucher->save();

        $this->makeLog()->fromActive()->to($state, "Archiving voucher");

        return $archivedVoucher;
    }

    private function makeLog(): VoucherActivityLog
    {
        return VoucherActivity::log($this->code, auth()->user());
    }
}
