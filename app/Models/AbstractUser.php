<?php

namespace App\Models;

use App\Models\Traits\AbstractModel;
use App\Models\Traits\HasCustomer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User;
use Carbon\Carbon;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property  string       $id
 * @property  string       $name
 * @property  string|null  $customer_id
 * @property  Carbon|null  $deleted_at
 * @property  Carbon       $created_at
 * @property  Carbon       $updated_at
 *
 * @property-read  Collection<Permission>  $permissions
 * @property-read  Collection<Role>        $roles
 *
 * @method  Builder|static  role(array|Collection|int|Role|string $roles, string $guard = null, $without = false)
 * @method  static  Builder|static  role(array|Collection|int|Role|string $roles, string $guard = null, $without = false)
 */
abstract class AbstractUser extends User
{
    use HasRoles, SoftDeletes, HasUlids, AbstractModel, HasCustomer;

    protected $fillable = ["name", 'customer_id'];

    public function logColumns(): array
    {
        return ["name", "customer_id", "deleted_at", "created_at", "updated_at"];
    }
}
