<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property float $amount
 * @property string $request_comment
 * @property  string $status
 * @property string $approved_comment
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $resolved_by
 * @property int $customer_id
 *
 * @property  User  $resolver
 */
class Finance extends Model
{
    use HasFactory;

    const STATUS_APPROVED = "approved";
    const STATUS_PENDING = "pending";
    const STATUS_CANCELED = "canceled";
    const STATUS_REJECTED = "rejected";

    protected $fillable = [
        'amount',
        'request_comment',
        'status',
        'approved_comment',
        'resolved_by',
        'customer_id'
    ];

//    public function transaction(): MorphOne
//    {
//        return $this->morphOne(Transaction::class,'model');
//    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, "resolved_by");
    }
}
