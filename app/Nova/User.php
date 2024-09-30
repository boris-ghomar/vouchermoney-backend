<?php

namespace App\Nova;

use App\Models\User as Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Nova\Fields\Avatar;
use App\Nova\Fields\BelongsTo;
use App\Nova\Fields\BelongsToMany;
use App\Nova\Fields\Hidden;
use App\Nova\Fields\ID;
use App\Nova\Fields\Password;
use App\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\DependencyContainer\DependencyContainer;
use App\Models\Permission as PermissionModel;

/**
 * @mixin Model
 */
class User extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static string $model = Model::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'name', 'email', 'id'
    ];

    public function title(): string
    {
        return $this->name;
    }

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        $user = $request->user();

        if ($user) $query->whereNot("id", $user->id);

        if ($request->viaRelationship) return $query;

        return $query->where("customer_id", $user->customer_id);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make(__("fields.id"), "id")->sortable()
                ->onlyForAdmins(),

            Text::make(__("fields.name"), "name")
                ->rules('required', 'max:100')->sortable(),

            Avatar::make(__("fields.avatar"), "customer.avatar")
                ->canSee(fn() => $this->is_customer)->exceptOnForms(),

            Text::make(__("fields.email"), "email")->sortable()
                ->rules('required', 'email:dns', 'max:255')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

//            Boolean::make(__("fields.is_verified"), "email_verified_at")->hide(),

//            DateTime::make(__("fields.email_verified_at"), "email_verified_at")->hide(),

            Password::make(__("fields.password"), "password")
                ->onlyOnForms()
                ->rules("min:8", PasswordRule::defaults())
                ->creationRules('required')
                ->updateRules('nullable'),

            Hidden::make("Customer", "customer_id")
                ->seeIfCan("customer:user:create")
                ->fillUsing(function ($request, $model, $attribute) {
                    $model->{$attribute} = $request->user()->customer_id;
                })->onlyOnForms(),

            BelongsTo::make(__("fields.customer"), "customer", Customer::class)->onlyOnDetail()
                ->canSee(fn(Request $request) => $request->user()?->id === $this->id && $request->user()->can("customer:view-balance")),

            DependencyContainer::make([
                BelongsTo::make(__("fields.customer"), "customer", Customer::class)
            ])->dependsOnNullOrZero("customer_id")->exceptOnForms()->canSee(fn(Request $request) => $this->customer_id),

            BelongsToMany::make(__("fields.permissions"), "permissions", Permission::class)
                ->canSee(function (Request $request) {
                    $user = $request->user();

                    return $user && $user->id !== $this->id && (
                            ($user->is_customer && $this->customer_id === $user->customer_id && $user->can("customer:user:attach-permission")) ||
                            ($user->is_admin && $user->can("user:attach-permission"))
                        );
                })->collapsable()->collapsedByDefault()
        ];
    }

    public static function relatablePermissions(NovaRequest $request, $query)
    {
        return $query->whereNotIn('name', PermissionModel::getAllHighOrderPermissions());
    }

    public function authorizedToAttachAny(NovaRequest $request, $model): bool
    {
        $user = $request->user();

        if (
            ($user && $this->id !== $user->id) &&
            (
                ($user->is_admin && $this->is_admin && $user->can("user:attach-permission")) ||
                ($user->is_customer && $this->customer_id === $user->customer_id && $user->can("customer:user:attach-permission"))
            )
        ) return true;

        return false;
    }

    public function authorizedToDetach(NovaRequest $request, $model, $relationship): bool
    {
        return $this->authorizedToAttachAny($request, $model);
    }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }

    /**
     * Get the cards available for the request.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }
}
