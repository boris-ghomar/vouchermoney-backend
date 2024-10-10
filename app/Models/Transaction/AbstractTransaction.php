<?php

namespace App\Models\Transaction;

use App\Models\Traits\AbstractModel;
use App\Models\Traits\HasAmount;
use App\Models\Traits\HasCustomer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property  string       $id
 * @property  string       $customer_id
 * @property  float        $amount
 * @property  string       $description
 * @property  string|null  $transactionable_type
 * @property  string|null  $transactionable_id
 * @property  Carbon       $created_at
 * @property  Carbon       $updated_at
 *
 * @property  Model|null  $transactionable
 */
abstract class AbstractTransaction extends Model
{
    use HasAmount, AbstractModel, HasCustomer;

    protected $keyType = "string";
    public $incrementing = false;

    protected $fillable = [
        'customer_id',
        'amount',
        'description',
        'transactionable_type',
        'transactionable_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        "amount" => "decimal:2",
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    public function transactionable(): MorphTo
    {
        return $this->morphTo("transactionable");
    }

    public function logColumns(): array
    {
        return ['customer_id', 'amount', 'description', 'transactionable_type', 'transactionable_id', 'created_at', 'updated_at'];
    }
}
