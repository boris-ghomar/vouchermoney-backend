<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property  int          $id
 * @property  string       $name
 * @property  string       $email
 * @property  Carbon|null  $email_verified_at
 * @property  bool         $is_active
 * @property  int|null     $customer_id
 * @property  string       $role
 * @property  string       $password
 * @property  string|null  $api_token
 * @property  Carbon|null  $created_at
 * @property  Carbon|null  $updated_at
 *
 * @property  bool         $is_admin
 * @property  bool         $is_customer
 * @property  Customer     $customer
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    const ROLE_ADMIN = "admin";
    const ROLE_CUSTOMER = "customer";

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
        'is_active',
        'api_token',
        'customer_id',
        'role'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token'
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

    public function getIsAdminAttribute(): bool
    {
        return $this->role === User::ROLE_ADMIN;
    }

    public function getIsCustomerAttribute(): bool
    {
        return $this->role === User::ROLE_CUSTOMER;
    }
}
