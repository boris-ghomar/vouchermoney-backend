<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property  bool         $status True - approved, False - rejected
 * @property  string       $resolver_id
 * @property  string|null  $resolver_comment
 * @property  Carbon       $resolved_at
 *
 * @property-read  User  $resolver
 * @property-read  bool  $is_approved
 * @property-read  bool  $is_rejected
 *
 * @method  Builder|static  onlyApproved()
 * @method  Builder|static  onlyRejected()
 *
 * @method  static  Builder|static  onlyApproved()
 * @method  static  Builder|static  onlyRejected()
 */
class ArchivedFinance extends AbstractFinance
{
    public $timestamps = false;

    const STATUS_APPROVED = true;
    const STATUS_REJECTED = false;

    protected array $additional_fillable = [
        'status',
        'resolver_id',
        'resolver_comment',
    ];

    protected array $additional_casts = [
        "status" => "boolean",
        "resolved_at" => "datetime",
    ];

    protected array $additional_log_columns = [
        'status',
        'resolver_id',
        'resolver_comment',
        'resolved_at',
    ];

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, "resolver_id");
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function scopeOnlyApproved(Builder $query): void
    {
        $query->where("status", self::STATUS_APPROVED);
    }

    public function scopeOnlyRejected(Builder $query): void
    {
        $query->where("status", self::STATUS_REJECTED);
    }
}
