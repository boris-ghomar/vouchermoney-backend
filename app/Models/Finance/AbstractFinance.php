<?php

namespace App\Models\Finance;

use App\Models\Traits\AbstractModel;
use App\Models\Traits\HasAmount;
use App\Models\Traits\HasCustomer;
use App\Models\Traits\HasTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @property  string       $id
 * @property  string       $customer_id
 * @property  string       $requester_id
 * @property  float        $amount
 * @property  string|null  $requester_comment
 * @property  Carbon       $created_at
 * @property  Carbon       $updated_at
 *
 * @property-read  User   $requester
 */
abstract class AbstractFinance extends Model
{
    use LogsActivity, HasAmount, HasTransaction, AbstractModel, HasCustomer;

    protected $keyType = "string";
    public $incrementing = false;

    protected $fillable = [
        "id",
        "customer_id",
        "requester_id",
        "amount",
        "requester_comment",
        "created_at",
        "updated_at"
    ];

    protected $casts = [
        "amount" => "decimal:2",
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, "requester_id");
    }

    public function logColumns(): array
    {
        return ['customer_id', 'requester_id', 'amount', 'requester_comment', 'created_at', 'updated_at'];
    }
}
