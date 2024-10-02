<?php

namespace App\Nova;

use App\Models\Permission as PermissionModel;
use App\Models\User as Model;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;

class Admin extends User
{
    public static function label(): string
    {
        return __("resources.admins");
    }

    public static function singularLabel(): string
    {
        return __("resources.admin");
    }

    public static $globallySearchable = true;

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        /** @var Model $user */
        $user = $request->user();

        if (!$user || !$user->is_admin) return $query->whereRaw("1 = 0");

        return $query
            ->whereNull("customer_id")
            ->whereNot("id", $user->id);
    }

    public static function relatablePermissions(NovaRequest $request, $query)
    {
        /** @var Model $user */
        $user = $request->user();

        if (!$user) return $query;

        return $query->whereIn('name', PermissionModel::getAvailableAdminPermissionsForUser($user));
    }
}
