<?php

namespace App\Nova;

use App\Nova\Fields\DateTime;
use App\Nova\Fields\FieldHelper;
use Illuminate\Http\Request;
use App\Nova\Fields\Code;
use App\Nova\Fields\ID;
use App\Nova\Fields\MorphTo;
use App\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Spatie\Activitylog\Models\Activity as Model;

class ActivityLog extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<Model>
     */
    public static string $model = Model::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ["log_name"];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return FieldHelper::make([
            ID::make(__("fields.id"), "id")->sortable(),

            Text::make(__('fields.name'), 'log_name'),
            Text::make(__('fields.event'), 'event')->onlyOnDetail(),
            Text::make(__('fields.description'), 'description')->onlyOnDetail(),
            Text::make(__("fields.batch_uuid"), "batch_uuid")->onlyOnDetail(),
            MorphTo::make(__("fields.subject"), "subject"),
            Text::make(__('fields.subject_id'), 'subject_id')->onlyOnDetail(),
            Text::make(__('fields.subject_type'), 'subject_type')->onlyOnDetail(),
            Text::make(__('fields.causer_ip'), 'properties->ip')->onlyOnIndex(),
            MorphTo::make(__('fields.causer'), 'causer'),
            Code::make(__('fields.properties'), 'properties')->json()->onlyOnDetail(),

            DateTime::timestamps()
        ]);
    }

    public static function authorizedToCreate(Request $request): false
    {
        return false;
    }

    public function authorizedToReplicate(Request $request): false
    {
        return false;
    }

    public function authorizedToDelete(Request $request): false
    {
        return false;
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
