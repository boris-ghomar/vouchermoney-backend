<?php

namespace App\Nova;

use App\Nova\Actions\AttachPermissionToApiToken;
use App\Nova\Actions\CreateCustomerApiToken;
use App\Nova\Fields\BelongsToMany;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\Text;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\CustomerApiToken as Model;

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

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Name', 'name')->sortable(),
            MorphToMany::make(__("fields.permissions"), "permissions", Permission::class)
                ->collapsable()->collapsedByDefault(),
            DateTime::make('Expires At', 'expires_at')
                ->rules('nullable', 'date')
                ->showOnDetail(),
            DateTime::make('Last Used At', 'last_used_at')
                ->sortable()
                ->showOnDetail(),
        ];
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
        return [
            new CreateCustomerApiToken(),
        ];
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }
    public function authorizedToAttach(NovaRequest $request, $model): false
    {
        return false;
    }
}
