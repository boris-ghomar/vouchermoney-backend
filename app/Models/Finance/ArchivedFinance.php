<?php

namespace App\Models\Finance;

use App\Models\Customer\Customer;
use App\Models\Transaction\ArchivedTransaction;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;

/**
 * @property  string       $id
 * @property  float        $amount
 * @property  bool         $status
 * @property  array        $customer_data
 * @property  array        $requester_data
 * @property  array        $resolver_data
 * @property  string|null  $requester_comment
 * @property  string|null  $resolver_comment
 * @property  Carbon       $resolved_at
 * @property  Carbon       $created_at
 * @property  Carbon       $updated_at
 *
 * @property-read  Customer|null  $customer
 * @property-read  User|null      $requester
 * @property-read  User|null      $resolver
 * @property-read  bool           $is_approved
 * @property-read  bool           $is_rejected
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
        'id',
        'amount',
        'status',
        'customer_data',
        'requester_data',
        'resolver_data',
        'requester_comment',
        'resolver_comment',
        "created_at",
        "updated_at"
    ];

    protected $casts = [
        "customer_data" => "array",
        "requester_data" => "array",
        "resolver_data" => "array",

        "status" => "boolean",
        "amount" => "decimal:2",

        "resolved_at" => "datetime",
        "created_at" => "datetime",
        "updated_at" => "datetime",
    ];

    public function getCustomerAttribute(): Customer|null
    {
        return !empty($this->customer_data["id"]) ? Customer::find($this->customer_data["id"]) : null;
    }

    public function getRequesterAttribute(): Customer|null
    {
        return !empty($this->requester_data["id"]) ? User::find($this->requester_data["id"]) : null;
    }

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

    /**
     * Create approved archived finance
     *
     * @param   Finance  $finance
     * @param   User     $resolver
     * @param   string   $resolver_comment
     * @return  static
     */
    public static function approved(Finance $finance, User $resolver, string $resolver_comment = ""): static
    {
        return static::make($finance, $resolver, static::STATUS_APPROVED, $resolver_comment);
    }

    /**
     * Create rejected archived finance
     *
     * @param   Finance  $finance
     * @param   User     $resolver
     * @param   string   $resolver_comment
     * @return  static
     */
    public static function rejected(Finance $finance, User $resolver, string $resolver_comment = ""): static
    {
        return static::make($finance, $resolver, static::STATUS_REJECTED, $resolver_comment);
    }

    private static function make(Finance $finance, User $resolver, bool $status, string $resolver_comment = ""): static
    {
        $archived = new static();
        $archived->id = $finance->id;
        $archived->amount = $finance->amount;
        $archived->status = $status;

        $archived->customer_data = $finance->customer;
        $archived->requester_data = $finance->requester;
        $archived->resolver_data = $resolver;

        if (! empty($finance->comment)) $archived->requester_comment = $finance->comment;
        if (! empty($resolver_comment)) $archived->resolver_comment = $resolver_comment;

        $archived->created_at = $finance->created_at;
        $archived->updated_at = $finance->updated_at;
        $archived->save();

        return $archived;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'amount',
                'status',
                'customer_data',
                'requester_data',
                'resolver_data',
                'requester_comment',
                'resolver_comment',
                'resolved_at',
                'created_at',
                'updated_at'
            ]);
    }

    public function transaction(): MorphOne
    {
        return $this->morphOne(Transaction::class, "model");
    }

    public function archived_transaction(): MorphOne
    {
        return $this->morphOne(ArchivedTransaction::class, "model");
    }
}
