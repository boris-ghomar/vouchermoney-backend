<?php

namespace App\Nova;

use App\Models\User;
use App\Nova\Fields\BelongsTo;
use App\Nova\Fields\Code;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\ID;
use App\Nova\Fields\Text;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\CustomerApiTokenActivity as Model;
use App\Models\Permission as PermissionModel;

/**
 * @mixin Model
 */
class CustomerApiTokenActivity extends Resource
{
    public static string $model = Model::class;
    public static $title = 'id';
    public static $search = ['id', 'request', "response"];
    public static $globallySearchable = false;

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        parent::indexQuery($request, $query);

        /** @var User $user */
        $user = $request->user();

        if (! ($user->is_customer_admin || $user->can(PermissionModel::CUSTOMERS_VIEW)))
            static::hideQuery($query);

        return $query;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make("Token", "token", CustomerApiToken::class),
            Text::make("Action", "action"),
            Code::make("Request", "request")->readonly(),
            Code::make("Response", "response")->readonly(),
            Code::make("Properties", "properties")->readonly(),
            DateTime::timestamps()
        ];
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }

    public function authorizedToForceDelete(Request $request): bool
    {
        return false;
    }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }

    public function authorizedToRestore(Request $request): bool
    {
        return false;
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public static function authorizedToViewAny(Request $request): bool
    {
        /** @var User $user */
        $user = $request->user();

        return $user->is_customer_admin || $user->can(PermissionModel::CUSTOMERS_VIEW);
    }

    public function authorizedToView(Request $request): bool
    {
        /** @var User $user */
        $user = $request->user();

        return $this->token->customer_id === $user->customer_id;
    }
}
