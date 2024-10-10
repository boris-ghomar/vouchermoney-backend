<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property  string       $email
 * @property  Carbon|null  $email_verified_at
 * @property  string       $password
 * @property  string|null  $remember_token
 *
 * @property-read  bool  $is_admin
 * @property-read  bool  $is_customer
 * @property-read  bool  $is_super
 * @property-read  bool  $is_customer_admin
 *
 * @method  Builder|static  onlyAdmins()
 * @method  Builder|static  onlyCustomers()
 * @method  static  Builder|static  onlyAdmins()
 * @method  static  Builder|static  onlyCustomers()
 */
class User extends AbstractUser
{
    use Notifiable, HasApiTokens;

    protected array $additional_fillable = ['email', 'email_verified_at', 'password'];
    protected array $additional_log_columns = ["email", "email_verified_at", "roles", "permissions"];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['email_verified_at' => 'datetime', 'password' => 'hashed'];

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

    public function isOwnerOf(User $user): bool
    {
        return $this->id !== $user->id && $this->is_customer_admin && $this->customer_id === $user->customer_id;
    }

    public function scopeOnlyAdmins(Builder $query): void
    {
        $query->whereNull("customer_id");
    }

    public function scopeOnlyCustomers(Builder $query): void
    {
        $query->whereNotNull("customer_id");
    }

    public static function administrator(): User
    {
        return static::role(Role::SUPER_ADMIN)->first();
    }
}
