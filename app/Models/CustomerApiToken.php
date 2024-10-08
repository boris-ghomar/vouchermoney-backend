<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Spatie\Permission\Traits\HasRoles;
/**
 * @property string $customer_id,
 * @property string $token,
 * @property string $name
 * @property Carbon $expires_at
 * @property Carbon $last_used_at
 * @property Customer $customer
 *
 * @method static|Builder findByToken(string $token)
 */
class CustomerApiToken extends Model
{
    use Authenticatable;

    use HasUlids, HasRoles;

    protected $fillable = [
        'customer_id',
        'token',
        'name',
        'expires_at',
        'last_updated_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isExpired(): bool
    {
        return $this->is_expires && $this->is_expires < now();
    }

    public function scopeFindByToken(Builder $query, string $token)
    {
        return $query->where("token", $token)->first();
    }
}
