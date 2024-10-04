<?php

namespace App\Nova;

use App\Models\Permission as PermissionModel;
use App\Models\User as Model;
use App\Nova\Fields\BelongsTo;
use App\Nova\Fields\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Permission;

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

        if ($request->viaRelationship && ($user->is_super || ($user->is_admin && $user->can(Permission::CUSTOMERS_VIEW))))
            return parent::indexQuery($request, $query);

        if (!$user->is_customer || !$user->can(Permission::CUSTOMER_USER_VIEW))
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

    public static function relatablePermissions(NovaRequest $request, $query)
    {
        return $query->whereIn('name', PermissionModel::$customerPermissions);
    }
}
