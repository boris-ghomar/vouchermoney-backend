<?php

namespace App\Nova;

use App\Models\User as Model;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Role as RoleModel;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Model
 */
class OwnUsers extends User
{
    public static function label(): string
    {
        return auth()->user()?->hasRole(RoleModel::SUPER_ADMIN) ? "Admins" : "Users";
    }

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        return $query->where("parent_id", $request->user()?->id);
    }
}
