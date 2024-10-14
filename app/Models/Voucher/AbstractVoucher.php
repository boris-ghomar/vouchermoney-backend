<?php

namespace App\Models\Voucher;

use App\Models\AbstractUser;
use App\Models\Customer;
use App\Models\Traits\AbstractModel;
use App\Models\Traits\HasCustomer;
use App\Models\Traits\HasTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property  string  $id
 * @property  string  $code
 * @property  float   $amount
 * @property  string  $customer_id
 * @property  string  $creator_type
 * @property  string  $creator_id
 * @property  Carbon  $created_at
 * @property  Carbon  $updated_at
 *
 * @property  Customer      $customer
 * @property  AbstractUser  $creator
 */
abstract class AbstractVoucher extends Model
{
    use LogsActivity, AbstractModel, HasTransaction, HasCustomer;

    protected $keyType = "string";
    public $incrementing = false;

    protected $fillable = [
        "code",
        "amount",
        'customer_id',
        'creator_type',
        'creator_id',
        "created_at",
        "updated_at"
    ];

    protected $casts = [
        "amount" => "decimal:2",
        "created_at" => "datetime",
        "updated_at" => "datetime"
    ];

    public function logColumns(): array
    {
        return [
            'code',
            'amount',
            'customer_id',
            'creator_type',
            'creator_id',
            'created_at',
            'updated_at'
        ];
    }

    public function creator(): MorphTo
    {
        return $this->morphTo("creator");
    }

    public static function findByCode(string $code): static|null
    {
        return static::query()->where("code", $code)->first();
    }
}
