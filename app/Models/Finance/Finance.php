<?php

namespace App\Models\Finance;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\DB;

/**
 * @property string|null $comment
 */
class Finance extends AbstractFinance
{
    use HasUlids;

    protected $fillable = [
        'amount',
        'comment',
        'customer_id'
    ];

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

    private function archive(User $resolver, bool $status, string $resolvedComment = ""): ArchivedFinance
    {
        return DB::transaction(function () use ($resolver, $status, $resolvedComment) {
            $archivedFinance = ArchivedFinance::make($this, $resolver, $status, $resolvedComment);

            $this->delete();

            return $archivedFinance;
        });
    }

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

    public function cancel(): void
    {
        DB::transaction(function () {
            if ($this->is_withdraw) $this->customer->deposit($this->amount, "Withdraw finance request cancelled");

            $this->delete();
        });
    }
}
