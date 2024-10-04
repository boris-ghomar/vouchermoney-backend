<?php

namespace App\Models\Finance;

use App\Models\Customer\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;

/**
 * @property string       $id
 * @property string       $customer_id
 * @property string|null  $requester_id
 * @property float        $amount
 * @property string|null  $comment
 * @property Carbon       $created_at
 * @property Carbon       $updated_at
 *
 * @property-read  Customer   $customer
 * @property-read  User|null  $requester
 */
class Finance extends AbstractFinance
{
    use HasUlids;

    protected $fillable = [
        'amount',
        'comment',
        'customer_id',
        'requester_id',
    ];

    protected $casts = ["amount" => "decimal:2"];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cancel(): void
    {
        DB::transaction(function () {
            if ($this->is_withdraw)
                $this->customer->deposit($this->amount, "Withdraw finance request cancelled", $this);

            $this->delete();
        });
    }

    public function approve(User $resolver, string $resolver_comment = ""): ArchivedFinance
    {
        return $this->archive(ArchivedFinance::STATUS_APPROVED, $resolver, $resolver_comment);
    }

    public function reject(User $resolver, string $resolver_comment = ""): ArchivedFinance
    {
        return $this->archive(ArchivedFinance::STATUS_REJECTED, $resolver, $resolver_comment);
    }

    private function archive(bool $status, User $resolver, string $resolver_comment = ""): ArchivedFinance
    {
        return DB::transaction(function () use ($status, $resolver, $resolver_comment) {
            $method = $status ? "approved" : "rejected";

            /** @var ArchivedFinance $archived */
            $archived = ArchivedFinance::{$method}($this, $resolver, $resolver_comment);

            if ($status === $this->is_deposit) {
                $description = ($status ? "Deposit" : "Withdraw") .  " finance request " . ($status ? "approved" : "rejected");
                $this->customer->deposit($this->amount, $description, $archived);
            }

            $this->customer->sendFinanceResolvedNotification($archived);

            $this->delete();

            return $archived;
        });
    }

    public static function deposit(User $requester, Customer $customer, float $amount, string $comment = ""): static
    {
        return DB::transaction(function () use ($customer, $amount, $comment, $requester) {
            $finance = static::make($requester, $customer, abs($amount), $comment);

            $customer->sendFinanceRequestedNotification($finance);

            return $finance;
        });
    }

    public static function withdraw(User $requester, Customer $customer, float $amount, string $comment = ""): static
    {
        return DB::transaction(function () use ($customer, $amount, $comment, $requester) {
            $finance = static::make($requester, $customer, abs($amount) * -1, $comment);

            $customer->withdraw($amount, "Make withdrawal finance request", $finance);

            $customer->sendFinanceRequestedNotification($finance);

            return $finance;
        });
    }

    private static function make(User $requester, Customer $customer, float $amount, string $comment = ""): static
    {
        $finance = new static();
        $finance->customer_id = $customer->id;
        $finance->amount = $amount;
        $finance->requester_id = $requester->id;

        if (! empty($comment)) $finance->comment = $comment;

        $finance->save();

        return $finance;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['customer_id', 'requester_id', 'amount', 'comment', 'created_at', 'updated_at']);
    }
}
