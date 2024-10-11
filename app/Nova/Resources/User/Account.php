<?php

namespace App\Nova\Resources\User;

use App\Models\Permission;
use App\Models\Permission as PermissionModel;
use App\Models\User as Model;
use App\Nova\Customer;
use App\Nova\Fields\BelongsTo;
use App\Nova\Fields\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;

class Account extends User
{
    public static function label(): string
    {
        return __("resources.accounts");
    }

    public static function singularLabel(): string
    {
        return __("resources.account");
    }

    public static $globallySearchable = true;

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        /** @var Model $user */
        $user = $request->user();

        if (!$user) return $query->whereRaw("1 = 0");

        if ($user->is_super || $request->viaRelationship && ($user->is_admin && $user->can(PermissionModel::CUSTOMERS_VIEW)))
            return parent::indexQuery($request, $query);

        if (!$user->is_customer_admin && !$user->can(PermissionModel::CUSTOMER_USER_VIEW))
            return $query->whereRaw("1 = 0");

        return $query->where("customer_id", $user->customer_id)
            ->whereNot("id", $user->id);
    }

    public function fields(NovaRequest $request): array
    {
        return array_merge(parent::fields($request), [
            BelongsTo::make(__("fields.customer"), "customer", Customer::class)
                ->exceptOnForms()->onlyForAdmins([PermissionModel::CUSTOMERS_VIEW]),
            Hidden::make(__("fields.customer"), "customer_id")
                ->onlyForCustomersAdmin()->onlyOnForms()
                ->hideWhenUpdating()->fillUsing(function ($request, $model, $attribute) {
                    $model->{$attribute} = $request->user()->customer_id;
                }),
        ]);
    }

    public static function authorizedToCreate(Request $request): bool
    {
        /** @var Model $user */
        $user = auth()->user();

        if ($request->viaResource === "customers" && ! empty(\App\Models\Customer::withTrashed()->find($request->viaResourceId)->deleted_at))
            return false;

        if ($request->resource === "customers" && ! empty(\App\Models\Customer::withTrashed()->find($request->resourceId)->deleted_at))
            return false;

        return $user && ($user->is_super || $user->is_customer_admin);
    }

    public static function relatablePermissions(NovaRequest $request, $query)
    {
        return $query->whereIn('name', PermissionModel::$customerPermissions);
    }
}
