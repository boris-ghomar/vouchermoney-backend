<?php

namespace App\Models\Customer;

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

/**
 * @property  string       $id
 * @property  string       $name
 * @property  float        $balance
 * @property  string       $type
 * @property  Carbon|null  $deleted_at
 * @property  Carbon       $created_at
 * @property  Carbon       $updated_at
 *
 * @property-read  Collection<User>  $users
 * @property-read  User              $admin
 */
class Customer extends Model
{
    use SoftDeletes, HasUlids, HasFinances, HasTransactions, HasNotifications, LogsActivity, HasVouchers, HasJsonRelationships;

    const TYPE_RESELLER = "reseller";
    const TYPE_MERCHANT = "merchant";

    protected $fillable = ['name', 'balance', 'type'];

    protected $casts = ["balance" => "decimal:2"];

    protected static function boot(): void
    {
        parent::boot();

        parent::deleted(function (Customer $model) {
            $model->users()->delete();
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function admin(): HasOne
    {
        return $this->hasOne(User::class)->oldestOfMany();
    }

    public static function makeMerchant(string $name, string $email, string $password): static
    {
        return static::make($name, static::TYPE_MERCHANT, $email, $password);
    }

    public static function makeReseller(string $name, string $email, string $password): static
    {
        return static::make($name, static::TYPE_RESELLER, $email, $password);
    }

    private static function make(string $customerName, string $type, string $email, string $password): static
    {
        return DB::transaction(function () use ($customerName, $type, $email, $password) {
            $customer = new static();
            $customer->name = $customerName;
            $customer->type = $type;
            $customer->save();

            $user = new User();
            $user->name = "Admin";
            $user->email = $email;
            $user->email_verified_at = now();
            $user->customer_id = $customer->id;
            $user->password = $password;
            $user->save();

            $user->assignRole(Role::CUSTOMER_ADMIN);

            return $customer;
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'balance', "available_balance", "type", "created_at", "updated_at", "deleted_at"]);
    }
}
