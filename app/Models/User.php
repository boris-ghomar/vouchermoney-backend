<?php

namespace App\Models;

use App\Models\Customer\Customer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
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
 * @property-read  Customer|null           $customer
 * @property-read  Collection<Permission>  $permissions
 * @property-read  Collection<Role>        $roles
 *
 * @property-read  bool           $is_admin
 * @property-read  bool           $is_customer
 * @property-read  bool           $is_super
 * @property-read  bool           $is_customer_admin
 * @property-read  string         $full_name
 */
class User extends Authenticatable
{
    use Notifiable, HasApiTokens, HasRoles, SoftDeletes, HasUlids, LogsActivity;

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

    public function getFullNameAttribute(): string
    {
        return $this->name . " [" . $this->customer->name . "]";
    }

    public function isOwnerOf(User $user): bool
    {
        return $this->id !== $user->id && $this->is_customer_admin && $this->customer_id === $user->customer_id;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                "email",
                "email_verified_at",
                "customer_id",
                "created_at",
                "updated_at",
                "deleted_at",
                "is_admin",
                "is_super",
                "is_customer_admin",
                "is_customer",
                "roles",
                "permissions"
            ]);
    }

    public function canAdmin(string $ability): bool
    {
        return $this->is_super || ($this->is_admin && $this->can($ability));
    }

    public function canCustomer(string $ability): bool
    {
        return $this->is_customer_admin || ($this->is_customer && $this->can($ability));
    }
}
