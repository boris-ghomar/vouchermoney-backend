<?php

namespace App\Nova;

use App\Models\Transaction\Transaction as Model;
use App\Nova\Fields\DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Nova\Fields\Badge;
use App\Nova\Fields\BelongsTo;
use App\Nova\Fields\Currency;
use App\Nova\Fields\ID;
use App\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Transaction extends Resource
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
    public static $search = ['id'];

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        if ($request->user()?->is_customer)
            $query->where("customer_id", $request->user()->customer_id);

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
            ID::make(__("fields.id"), "id")->sortable(),

            Currency::make(__("fields.amount"), "amount")
                ->filterable()->hideFromIndex(),

            Currency::make(__("fields.amount"), "amount")->displayAsPositive(),

            Badge::make(__("fields.type"), "type")->map([
                    "withdraw" => "danger",
                    "deposit" => "success"
                ])->labels([
                    "withdraw" => __("fields.withdraw"),
                    "deposit" => __("fields.deposit")
                ])->onlyOnIndex(),

            BelongsTo::make(__("fields.customer"), "customer")
                ->onlyForAdmins(),

            Text::make(__("fields.description"), "description"),

            DateTime::createdAt()->filterable()->sortable(),
            DateTime::updatedAt(),
        ];
    }

    public static function authorizedToCreate(Request $request): false
    {
        return false;
    }

    public function authorizedToDelete(Request $request): false
    {
        return false;
    }

    public function authorizedToForceDelete(Request $request): false
    {
        return false;
    }

    public function authorizedToReplicate(Request $request): false
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
