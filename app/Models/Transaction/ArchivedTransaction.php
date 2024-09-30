<?php

namespace App\Models\Transaction;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property  string       $id
 * @property  int          $customer_id
 * @property  float        $amount
 * @property  string|null  $description
 * @property  Carbon       $archived_at
 * @property  Carbon|null  $created_at
 * @property  Carbon|null  $updated_at
 *
 * @property  Customer     $customer
 */
class ArchivedTransaction extends Transaction
{
    protected $table = "archived_transactions";
    protected $keyType = "string";
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        "archived_at" => "datetime",
        "created_at" => "datetime",
        "updated_at" => "datetime"
    ];

    protected $fillable = [
        'customer_id',
        'amount',
        'archived_at'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
