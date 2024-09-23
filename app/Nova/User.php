<?php

namespace App\Nova;

use App\Models\User as Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Laravel\Nova\Fields\Avatar;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Password;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\DependencyContainer\DependencyContainer;

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
        'name', 'email'
    ];

    public function title(): string
    {
        return $this->name;
    }

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        $user = $request->user();

        if ($user) {
            $query->whereNot("id", $user->id);
        }

        if ($request?->viaRelationship) {
            return $query;
        }

        if ($user?->is_customer)
            return $query->where("customer_id", $user->customer_id);

        return $query->where("role", Model::ROLE_ADMIN);
    }

    public static function authorizedToViewAny(Request $request): bool
    {
        $user = $request->user();

        if ($user && (
                ($user->is_customer && $user->can("customer:user:view-any")) ||
                ($user->is_admin && $user->can("user:view-any")))
        ) return true;

        return false;
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
            ID::make(__("fields.id"), "id")->sortable()->canSee(function (Request $request) {
                $user = $request->user();
                return $user && $user->is_admin;
            }),

            Text::make(__("fields.name"), "name")->filterable()->rules('required', 'max:100'),

            Boolean::make(__("fields.is_active"), "is_active")
                ->exceptOnForms()
                ->canSee(function (Request $request) {
                    $user = $request->user();

                    return $user && (
                            $user->is_admin ||
                            ($user->is_customer && $user->customer_id === $this->customer_id)
                        );
                }),

            Avatar::make(__("fields.avatar"), "customer.avatar")
                ->canSee(fn() => $this->is_customer)
                ->exceptOnForms(),

            Badge::make(__("fields.role"), "role")
                ->exceptOnForms()
                ->map([
                    Model::ROLE_ADMIN => "danger",
                    Model::ROLE_CUSTOMER => "success"
                ])
                ->canSee(fn(Request $request) => $request->user()?->is_admin),

            Hidden::make(__("fields.role"), "role")
                ->onlyOnForms()->hideWhenUpdating()->fillUsing(function ($request, $model, $attribute) {
                    $model->{$attribute} = $request?->viaRelationship ? Model::ROLE_CUSTOMER : $request->user()?->role;
                }),

            Text::make(__("fields.email"), "email")
                ->filterable()
                ->rules('required', 'email:dns', 'max:255')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

//            Boolean::make(__("fields.is_verified"), "email_verified_at")->hide(),

//            DateTime::make(__("fields.email_verified_at"), "email_verified_at")->hide(),

            Password::make(__("fields.password"), "password")
                ->onlyOnForms()
                ->rules("min:8", Rules\Password::defaults())
                ->creationRules('required')
                ->updateRules('nullable'),

            BelongsTo::make("Customer", "customer", Customer::class)
                ->onlyOnForms()->readonly()->canSee(fn($request) => $request->viaRelationship),

            Hidden::make("Customer", "customer_id")
                ->canSee(function (Request $request) {
                    $user = $request->user();

                    return $user && $user->is_customer && $user->can("customer:user:create");
                })->fillUsing(function ($request, $model, $attribute) {
                    $model->{$attribute} = $request->user()->customer_id;
                }),

            DependencyContainer::make([
                BelongsTo::make(__("fields.customer"), "customer", Customer::class)
                    ->onlyOnDetail()
            ])->dependsOn("role", Model::ROLE_CUSTOMER),

            BelongsToMany::make(__("fields.permissions"), "permissions", Permission::class)
                ->canSee(function (Request $request) {
                    $user = $request->user();

                    return $user && (
                            ($user->is_customer && $user->can("customer:user:attach-permission")) ||
                            ($user->is_admin)
                        );
                })->collapsable()->collapsedByDefault()
        ];
    }

    public function authorizedToAttachAny(NovaRequest $request, $model): bool
    {
        $user = $request->user();
        if (
            ($user && $this->id !== $user->id) &&
            (
                ($user->is_admin && $this->is_admin && $user->can("user:attach-permission") && !in_array($model?->name, ["user:delete", "user:attach-permission"])) ||
                ($user->is_customer && $this->customer_id === $user->customer_id && $user->can("customer:user:attach-permission"))
            )
            && !in_array($model->name, [
                ]
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
