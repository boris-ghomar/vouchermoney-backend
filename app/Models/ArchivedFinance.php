<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $customer_id
 * @property float $amount
 * @property string $status
 * @property string|null $request_comment
 * @property string|null $resolved_comment
 * @property array $resolver_data
 * @property Carbon|null $expired_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Customer $customer
 * @property User|null $resolver
 *
 * @property bool $is_expired
 * @property bool $is_approved
 * @property bool $is_rejected
 *
 * @method  Builder|static  onlyExpired()
 * @method  Builder|static  onlyApproved()
 * @method  Builder|static  onlyRejected()
 */
class ArchivedFinance extends Model
{
    use HasFactory;

    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_EXPIRED = "expired";

    protected $fillable = [
        'customer_id',
        'amount',
        'request_comment',
        'status',
        'resolved_comment',
        'expired_at',
        'resolver_data',
    ];

    protected $casts = [
        "expired_at" => "datetime",
        "resolver_data" => "array"
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
     */
    public function resolver(): User|null
    {
        return User::find($this->resolver_data["id"]);
    }

    public function getIsExpiredAttribute(): bool
    {
        return !empty($this->expired_at);
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function scopeOnlyExpired(Builder $query): void
    {
        $query->whereNotNull("expired_at");
    }

    public function scopeOnlyApproved(Builder $query): void
    {
        $query->where("status", self::STATUS_APPROVED);
    }

    public function scopeOnlyRejected(Builder $query): void
    {
        $query->where("status", self::STATUS_REJECTED);
    }

    public static function make(Finance $finance, User $resolver, string $status, string $resolvedComment = ""): static
    {
        $archivedFinance = new static();
        $archivedFinance->customer_id = $finance->customer_id;
        $archivedFinance->resolver_data = $resolver->toJson();
        $archivedFinance->amount = $finance->amount;
        $archivedFinance->request_comment = $finance->comment;

        if (!empty($resolvedComment)) $archivedFinance->resolved_comment = $resolvedComment;

        if ($status === static::STATUS_EXPIRED)
            $archivedFinance->expired_at = now();

        $archivedFinance->status = $status === static::STATUS_EXPIRED ? static::STATUS_REJECTED : $status;
        $archivedFinance->created_at = $finance->created_at;
        $archivedFinance->updated_at = $finance->updated_at;
        $archivedFinance->save();

        return $archivedFinance;
    }
}
