<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property float $amount
 * @property string|null $request_comment
 * @property string $status
 * @property string|null $resolved_comment
 * @property Carbon $expired_at
 * @property int|null $resolved_by
 * @property int $customer_id
 * @property User $resolver
 * @property Carbon $created_at
 * @property  Carbon $updated_at
 */
class ArchivedFinance extends Model
{
    use HasFactory;

    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'amount',
        'request_comment',
        'status',
        'resolved_comment',
        'expired_at',
        'resolved_by',
        'customer_id'
    ];

    /**
     * Relationship to the customer
     * @return BelongsTo
     */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship to the user, who approve/reject a finance
     * @return BelongsTo
     */

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class,'resolved_by');
    }

}
