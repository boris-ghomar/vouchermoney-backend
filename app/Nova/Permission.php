<?php

namespace App\Nova;

use Illuminate\Http\Request;
use App\Nova\Fields\BelongsToMany;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\ID;
use App\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Permission as Model;

/**
 * @mixin Model
 */
class Permission extends Resource
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
    public static $search = [];

    public static $searchable = false;


    public function title(): string
    {
        return __("permissions." . $this->name);
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
            ID::make(__("fields.id"), "id")->onlyOnDetail(),

            Text::make(__("fields.name"), fn () => $this->title()),

            BelongsToMany::make(__("fields.users"), "users", User::class)
                ->onlyOnDetail()->onlyForAdmins(),

            DateTime::createdAt()->onlyOnDetail(),
            DateTime::updatedAt(),
        ];
    }

    public function authorizedToUpdate(Request $request): false
    {
        return false;
    }

    /**
     * Get the cards available for the request.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }
}
