<?php

namespace App\Models\Finance;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * @property  bool         $status
 * @property  string|null  $request_comment
 * @property  string|null  $resolved_comment
 * @property  array        $resolver_data
 * @property  Carbon       $resolved_at
 *
 * @property-read  User|null  $resolver
 * @property-read  bool       $is_approved
 * @property-read  bool       $is_rejected
 *
 * @method  Builder|static  onlyApproved()
 * @method  Builder|static  onlyRejected()
 */
class ArchivedFinance extends AbstractFinance
{
    protected $keyType = "string";
    public $incrementing = false;
    public $timestamps = false;

    const STATUS_APPROVED = true;
    const STATUS_REJECTED = false;

    protected $fillable = [
        'customer_id',
        'amount',
        'status',
        'request_comment',
        'resolved_comment',
        'resolver_data',
        'resolved_at',
    ];

    protected $casts = [
        "resolved_at" => "datetime",
        "resolver_data" => "array",
        "status" => "boolean",
        "created_at" => "datetime",
        "updated_at" => "datetime"
    ];

    /**
     * Relationship to the user, who approve/reject a finance
     */
    public function getResolverAttribute(): User|null
    {
        return !empty($this->resolver_data["id"]) ? User::find($this->resolver_data["id"]) : null;
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

    public static function makeApproved(Finance $finance, User $resolver, string $resolvedComment = ""): static
    {
        return static::make($finance, $resolver, static::STATUS_APPROVED, $resolvedComment);
    }

    public static function makeRejected(Finance $finance, User $resolver, string $resolvedComment = ""): static
    {
        return static::make($finance, $resolver, static::STATUS_REJECTED, $resolvedComment);
    }

    public static function make(Finance $finance, User $resolver, bool $status, string $resolvedComment = ""): static
    {
        $archivedFinance = new static();
        $archivedFinance->id = $finance->id;
        $archivedFinance->customer_id = $finance->customer_id;
        $archivedFinance->amount = $finance->amount;
        $archivedFinance->status = $status;
        $archivedFinance->user_id = $finance->user_id;

        if (!empty($finance->comment)) $archivedFinance->request_comment = $finance->comment;
        if (!empty($resolvedComment)) $archivedFinance->resolved_comment = $resolvedComment;

        $archivedFinance->resolver_data = $resolver->toJson();
        $archivedFinance->created_at = $finance->created_at;
        $archivedFinance->updated_at = $finance->updated_at;
        $archivedFinance->save();

        return $archivedFinance;
    }
}
