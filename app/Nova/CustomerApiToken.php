<?php

namespace App\Nova;

use App\Models\Permission as PermissionModel;
use App\Nova\Actions\CreateCustomerApiToken;
use App\Nova\Fields\BelongsToMany;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\FieldHelper;
use App\Nova\Fields\Text;
use Illuminate\Http\Request;
use App\Nova\Fields\ID;
use App\Nova\Fields\MorphToMany;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\CustomerApiToken as Model;
use function Webmozart\Assert\Tests\StaticAnalysis\false;

class CustomerApiToken extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static string $model = Model::class;

    public static function label(): string
    {
        return 'Api Tokens';
    }

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */

    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */

    public static $search = [
        'name', 'id'
    ];

    public static $globallySearchable = false;

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

            MorphToMany::make(__("fields.permissions"), "permissions", Permission::class)
                ->collapsable()
                ->collapsedByDefault(),

            DateTime::make('Expires At', 'expires_at')
                ->rules('nullable', 'date')
                ->sortable(),

            DateTime::make('Last Used At', 'last_used_at')
                ->sortable(),

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
}
