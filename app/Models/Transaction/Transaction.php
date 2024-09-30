<?php

namespace App\Models\Transaction;

use App\Exceptions\AttemptToArchiveTransactionWithoutCustomer;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @property  string       $id
 * @property  int          $customer_id
 * @property  float        $amount
 * @property  string|null  $description
 * @property  Carbon|null  $created_at
 * @property  Carbon|null  $updated_at
 *
 * @property  string       $type
 * @property  Customer     $customer
 */
class Transaction extends Model
{
    use HasUlids;

    protected $keyType = "string";
    public $incrementing = false;

    protected $fillable = [
        'customer_id',
        'amount',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getTypeAttribute(): string
    {
        return $this->amount < 0 ? "withdraw" : "deposit";
    }

    /**
     * @throws AttemptToArchiveTransactionWithoutCustomer
     */
    public function archive(): ArchivedTransaction
    {
        $customer = $this->customer;

        if (empty($customer))
            throw new AttemptToArchiveTransactionWithoutCustomer();

        return DB::transaction(function () use ($customer) {
            $customer->balance += $this->amount;
            $customer->save();

            $archivedTransaction = new ArchivedTransaction();
            $archivedTransaction->id = $this->id;
            $archivedTransaction->customer_id = $this->customer->id;
            $archivedTransaction->amount = $this->amount;
            $archivedTransaction->description = $this->description;
            $archivedTransaction->created_at = $this->created_at;
            $archivedTransaction->updated_at = $this->updated_at;
            $archivedTransaction->save();

            $this->delete();

            return $archivedTransaction;
        });
    }
}
