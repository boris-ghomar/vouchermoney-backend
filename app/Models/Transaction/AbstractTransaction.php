<?php

namespace App\Models\Transaction;

use App\Models\Customer\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property  string  $id
 * @property  string  $customer_id
 * @property  float   $amount
 * @property  string  $description
 * @property  string  $model_type
 * @property  string  $model_id
 * @property  Carbon  $created_at
 * @property  Carbon  $updated_at
 *
 * @property  string      $type
 * @property  Customer    $customer
 * @property  Model|null  $model
 *
 * @method  Builder|static  onlyWithdraws()
 * @method  Builder|static  onlyDeposits()
 */
abstract class AbstractTransaction extends Model
{
    protected $keyType = "string";
    public $incrementing = false;

    protected $fillable = [
        'customer_id',
        'amount',
        'description',
        'model_type',
        'model_id',
    ];

    protected $casts = [
        "amount" => "decimal:2"
    ];

    const TYPE_WITHDRAW = "withdraw";
    const TYPE_DEPOSIT = "deposit";

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo("model");
    }

    public function getTypeAttribute(): string
    {
        return $this->amount < 0 ? static::TYPE_WITHDRAW : static::TYPE_DEPOSIT;
    }

    public function scopeOnlyWithdraws(Builder $query): void
    {
        $query->where("amount", "<", 0);
    }

    public function scopeOnlyDeposits(Builder $query): void
    {
        $query->where("amount", ">", 0);
    }
}
