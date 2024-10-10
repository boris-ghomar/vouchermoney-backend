<?php

namespace App\Models;

use App\Models\Customer\Customer;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Foundation\Auth\User as AuthenticatableUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
class CustomerApiToken extends AuthenticatableUser implements AuthenticatableContract
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
    public function permissions(): BelongsToMany
    {
        return $this->morphToMany(Permission::class, 'model', 'model_has_permissions');
    }
}
