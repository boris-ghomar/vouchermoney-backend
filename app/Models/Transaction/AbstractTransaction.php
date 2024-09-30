<?php

namespace App\Models\Transaction;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property  string       $id
 * @property  string       $customer_id
 * @property  float        $amount
 * @property  string|null  $description
 * @property  Carbon|null  $created_at
 * @property  Carbon|null  $updated_at
 *
 * @property  string       $type
 * @property  Customer     $customer
 */
abstract class AbstractTransaction extends Model
{
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
}
