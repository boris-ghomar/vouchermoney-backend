<?php

namespace App\Nova;

use App\Models\Permission as PermissionModel;
use App\Nova\Actions\CreateCustomerApiToken;
use App\Models\User;
use App\Nova\Fields\BelongsTo;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\FieldHelper;
use App\Nova\Fields\HasMany;
use App\Nova\Fields\Text;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Nova\Fields\ID;
use App\Nova\Fields\MorphToMany;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\CustomerApiToken as Model;

class CustomerApiToken extends Resource
{
    public static string $model = Model::class;
    public static $title = 'name';
    public static $search = ['name', 'id'];
    public static $globallySearchable = false;

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        parent::indexQuery($request, $query);

        /** @var User $user */
        $user = $request->user();

        if (! ($user->can(PermissionModel::CUSTOMERS_VIEW) || $user->is_customer_admin))
            static::hideQuery($query);

        static::forCustomer($request, $query);

        return $query;
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
            ID::make()->sortable(),

            Text::make('Name', 'name')->sortable(),

            BelongsTo::make("Customer", "customer", Customer::class)
                ->onlyForAdmins([PermissionModel::CUSTOMERS_VIEW]),

            MorphToMany::make(__("fields.permissions"), "permissions", Permission::class)
                ->collapsable()
                ->collapsedByDefault(),

            HasMany::make("Activities", "tokenActivities", CustomerApiTokenActivity::class),

            DateTime::make('Expires At', 'expires_at')->sortable(),

            DateTime::make('Last Used At', 'last_used_at')->sortable(),

            DateTime::timestamps()
        ]);
    }

    /**
     * Get the actions available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [
            new CreateCustomerApiToken(),
        ];
    }

    /**
     * @param Request $request
     * @return bool
     */
    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }

    /**
     * @param NovaRequest $request
     * @param $model
     * @return bool
     */
    public function authorizedToAttach(NovaRequest $request, $model): bool
    {
        return false;
    }

    /**
     * @param NovaRequest $request
     * @param $model
     * @return bool
     */
    public function authorizedToAttachAny(NovaRequest $request, $model): bool
    {
        return false;
    }

    /**
     * @param NovaRequest $request
     * @param $model
     * @param $relationship
     * @return bool
     */
    public function authorizedToDetach(NovaRequest $request, $model, $relationship): bool
    {
        return false;
    }

    public static function label(): string
    {
        return 'Api Tokens';
    }

    public function getKey(): string
    {
        return "api-tokens";
    }
}
