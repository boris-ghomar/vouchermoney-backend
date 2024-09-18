<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property Carbon|null $email_verified_at
 * @property int $parent_id
 * @property boolean $is_active
 * @property string $timezone
 * @property string $api_key
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property User $parent
 * @property Collection<User> $children
 * @property Customer $customer
 * @property Collection<Voucher> $createdVouchers
 * @property Collection<Voucher> $updatedVouchers
 * @property Collection<Finance> $createdFinances
 * @property Collection<Finance> $updatedFinances
 *
 * @property bool is_parent
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'parent_id',
        'is_active',
        'timezone',
        'api_key',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_key'
    ];

    protected $with = ["roles", "permissions"];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function parent(): HasOne
    {
        return $this->hasOne(User::class, "parent_id", "id");
    }

    public function children(): HasMany
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function createdVouchers(): HasMany
    {
        return $this->hasMany(Voucher::class, 'created_by');
    }

    public function updatedVouchers(): HasMany
    {
        return $this->hasMany(Voucher::class, 'updated_by');
    }

    public function createdFinances(): HasMany
    {
        return $this->hasMany(Finance::class, 'created_by');
    }

    public function updatedFinances(): HasMany
    {
        return $this->hasMany(Finance::class, 'updated_by');
    }

    public function addIsParentAttribute(): bool
    {
        return $this->parent_id === 0;
    }
}
