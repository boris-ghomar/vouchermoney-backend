<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property  string       $id
 * @property  string       $name
 * @property  string       $email
 * @property  Carbon|null  $email_verified_at
 * @property  string|null  $customer_id
 * @property  string       $password
 * @property  string|null  $remember_token
 * @property  Carbon|null  $deleted_at
 * @property  Carbon|null  $created_at
 * @property  Carbon|null  $updated_at
 *
 * @property  bool           $is_admin
 * @property  bool           $is_customer
 * @property  bool           $is_super
 * @property  bool           $is_customer_admin
 * @property  Customer|null  $customer
 * @property  string         $full_name
 *
 * @property  Collection<Permission>  $permissions
 */
class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasRoles, SoftDeletes, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'customer_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getIsSuperAttribute(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN);
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->customer_id === null;
    }

    public function getIsCustomerAdminAttribute(): bool
    {
        return $this->hasRole(Role::CUSTOMER_ADMIN);
    }

    public function getIsCustomerAttribute(): bool
    {
        return $this->customer_id !== null;
    }

    public function canSeeVouchersList(): bool
    {
        return $this->is_customer_admin || $this->canAny([Permission::CUSTOMER_VOUCHER_VIEW, Permission::VOUCHERS_VIEW]) || $this->is_super;
    }

    public function getFullNameAttribute(): string
    {
        return $this->name . " [" . $this->customer->name . "]";
    }

    public function isOwnerOf(User $user): bool
    {
        return $this->id !== $user->id && $this->is_customer_admin && $this->customer_id === $user->customer_id;
    }
}
