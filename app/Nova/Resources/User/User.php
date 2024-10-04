<?php

namespace App\Nova\Resources\User;

use App\Models\User as Model;
use App\Nova\Fields\BelongsToMany;
use App\Nova\Fields\FieldHelper;
use App\Nova\Fields\ID;
use App\Nova\Fields\Password;
use App\Nova\Fields\Text;
use App\Nova\Permission;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Laravel\Nova\Http\Requests\NovaRequest;

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

    public static $globallySearchable = false;

    public function title(): string
    {
        return $this->name;
    }

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        /** @var Model $user */
        $user = $request->user();

        if (!$user) return $query->whereRaw("1 = 0");

        if ($request->viaRelationship) return $query;

        return $query->where("customer_id", $user->customer_id)
            ->whereNot("id", $user->id);
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return FieldHelper::make([
            ID::make(__("fields.id"), "id")->sortable()
                ->onlyForAdmins(),

            // "name" attribute
            $this->getNameFields(),

            Text::make(__("fields.email"), "email")->sortable()
                ->rules('required', 'email:dns', 'max:255')
                ->creationRules('unique:users,email')
                ->updateRules('unique:users,email,{{resourceId}}'),

            Password::make(__("fields.password"), "password")
                ->onlyOnForms()
                ->rules("min:8", PasswordRule::defaults())
                ->creationRules('required')
                ->updateRules('nullable'),

            BelongsToMany::make(__("fields.permissions"), "permissions", Permission::class)
                ->canSee(function (Request $request) {
                    /** @var Model $user */
                    $user = $request->user();

                    if (!$user || $user->id === $this->id)
                        return false;

                    return $user->is_super || $user->is_customer_admin;
                })->collapsable()->collapsedByDefault()
        ]);
    }

    public static function relatablePermissions(NovaRequest $request, $query)
    {
        /** @var Model $user */
        $user = $request->user();

        if (!$user) return $query;

        return $query->whereIn('name', $user->getPermissionNames());
    }

    // FIXME:
//    public function authorizedToAttachAny(NovaRequest $request, $model): bool
//    {
//        $user = $request->user();
//
//        if (
//            ($user && $this->id !== $user->id) &&
//            (
//                ($user->is_admin && $this->is_admin && $user->can("user:attach-permission")) ||
//                ($user->is_customer && $this->customer_id === $user->customer_id && $user->can("customer:user:attach-permission"))
//            )
//        ) return true;
//
//        return false;
//    }

//    public function authorizedToDetach(NovaRequest $request, $model, $relationship): bool
//    {
//        return $this->authorizedToAttachAny($request, $model);
//    }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }

    private function getNameFields(): array
    {
        return [
            Text::make(__("fields.name"), "name")->sortable()->exceptOnForms(),

            Text::make(__("fields.name"), "name")->onlyOnForms()
                ->hideWhenCreating()
                ->rules('required', 'max:100')->canSee(function (Request $request) {
                    /** @var Model $user */
                    $user = $request->user();

                    if (!$user) return false;

                    return $user->is_super || ($user->is_customer_admin && $user->id !== $this->id && $user->customer_id === $this->customer_id);
                }),

            Text::make(__("fields.name"), "name")->onlyOnForms()
                ->hideWhenUpdating()
                ->rules('required', 'max:100')->canSee(function (Request $request) {
                    /** @var Model $user */
                    $user = $request->user();

                    if (!$user) return false;

                    return $user->is_super || $user->is_customer_admin;
                })
        ];
    }
}
