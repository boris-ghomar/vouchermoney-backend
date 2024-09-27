<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $customer_id
 * @property float $amount
 * @property string|null $comment
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property string $type
 * @property bool $is_deposit
 * @property bool $is_withdraw
 * @property Customer $customer
 *
 * @method  Builder|static  onlyWithdraws()
 * @method  Builder|static  onlyDeposits()
 */
class Finance extends Model
{
    protected $fillable = [
        'amount',
        'comment',
        'customer_id'
    ];

    const TYPE_WITHDRAW = "withdraw";

    const TYPE_DEPOSIT = "deposit";

    /**
     * Relationship to the customer
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo( Customer::class);
    }

    public function scopeOnlyWithdraws(Builder $query): void
    {
        $query->where("amount", "<", 0);
    }

    public function scopeOnlyDeposits(Builder $query): void
    {
        $query->where("amount", ">", 0);
    }

    public function getTypeAttribute(): string
    {
        return $this->amount < 0 ? static::TYPE_WITHDRAW : static::TYPE_DEPOSIT;
    }

    public function getIsDepositAttribute(): bool
    {
        return $this->amount > 0;
    }

    public function getIsWithdrawAttribute(): bool
    {
        return $this->amount < 0;
    }

    private static function make(Customer $customer, float $amount, string $comment = ""): static
    {
        $finance = new static();
        $finance->customer_id = $customer->id;
        $finance->amount = $amount;

        if (!empty($comment)) $finance->comment = $comment;

        $finance->save();

        return $finance;
    }

    public static function withdraw(Customer $customer, float $amount, string $comment = ""): static
    {
        return DB::transaction(function () use ($customer, $amount, $comment) {
            $customer->withdraw($amount, "Make withdrawal finance request");

            return static::make($customer, abs($amount) * -1, $comment);
        });
    }

    public static function deposit(Customer $customer, float $amount, string $comment = ""): static
    {
        return static::make($customer, abs($amount), $comment);
    }

    private function archive(User $resolver, string $status, string $resolvedComment = ""): ArchivedFinance
    {
        return DB::transaction(function () use ($resolver, $status, $resolvedComment) {
            $archivedFinance = ArchivedFinance::make($this, $resolver, $status, $resolvedComment);

            $this->delete();

            return $archivedFinance;
        });
    }

    /**
     * @throws \Exception
     */
    public function approve(User $resolver, string $approveComment = ""): ArchivedFinance
    {
        return DB::transaction(function () use ($approveComment, $resolver) {
            if ($this->is_deposit)
                $this->customer->deposit($this->amount, "Deposit finance request approved");

            return $this->archive($resolver, ArchivedFinance::STATUS_APPROVED, $approveComment);
        });
    }

    public function reject(User $resolver, string $rejectComment = ""): ArchivedFinance
    {
        return DB::transaction(function () use ($rejectComment, $resolver) {
            if ($this->is_withdraw)
                $this->customer->deposit($this->amount, "Withdraw finance request rejected");

            return $this->archive($resolver, ArchivedFinance::STATUS_REJECTED, $rejectComment);
        });
    }

    public function expire(User $resolver): ArchivedFinance
    {
        return DB::transaction(function () use ($resolver) {
            if ($this->is_withdraw)
                $this->customer->deposit($this->amount, "Withdraw finance request expired (rejected)");

            return $this->archive($resolver, ArchivedFinance::STATUS_EXPIRED, "Finance request expired");
        });
    }

    public function cancel(): void
    {
        DB::transaction(function () {
            if ($this->is_withdraw) $this->customer->deposit($this->amount, "Withdraw finance request cancelled");

            $this->delete();
        });
    }
}
