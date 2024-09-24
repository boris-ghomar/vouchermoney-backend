<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property  int               $id
 * @property  string            $name
 * @property  float             $balance
 * @property  string|null       $avatar
 * @property  string            $type
 * @property  Carbon|null       $created_at
 * @property  Carbon|null       $updated_at
 *
 * @property  Collection<User>  $users
 */
class Customer extends Model
{
    use HasFactory, SoftDeletes;

    const TYPE_RESELLER = "reseller";
    const TYPE_MERCHANT = "merchant";

    protected $fillable = [
        'name',
        'balance',
        'avatar',
        'type'
    ];

    protected static function boot(): void
    {
        parent::boot();

        parent::deleted(function (Customer $model) {
            $model->users()->delete();
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, "customer_id");
    }
}
