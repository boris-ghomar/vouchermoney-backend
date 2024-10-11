<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property  string       $token
 * @property  Carbon|null  $expires_at
 * @property  Carbon|null  $last_used_at
 *
 * @property-read  bool  $is_expired
 *
 * @property-read  Collection<CustomerApiTokenActivity>  $activities
 */
class CustomerApiToken extends AbstractUser
{
    protected array $additional_fillable = ['token', 'expires_at', 'last_updated_at'];
    protected array $additional_log_columns = ["expires_at", "last_used_at"];
    protected $casts = ['expires_at' => 'datetime', 'last_used_at' => 'datetime'];
    protected $hidden = ["token"];

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->lt(now());
    }

    public function permissions(): BelongsToMany
    {
        return $this->morphToMany(Permission::class, 'model', 'model_has_permissions');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(CustomerApiTokenActivity::class, "token_id");
    }

    public static function findByToken(string $token)
    {
        return static::query()->where("token", $token)->first();
    }
}
