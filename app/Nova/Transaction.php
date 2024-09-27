<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Transaction as Model;

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

            Currency::make(__("fields.amount"), fn(Model $transaction) => abs($transaction->amount))->onlyOnIndex(),

            Badge::make(__("fields.type"), "type")->map([
                    "withdraw" => "danger",
                    "deposit" => "success"
                ])->labels([
                    "withdraw" => __("fields.withdraw"),
                    "deposit" => __("fields.deposit")
                ])->onlyOnIndex(),

            BelongsTo::make(__("fields.customer"), "customer")
                ->canSee(fn(Request $request) => $request->user()?->is_admin),

            Text::make(__("fields.description"), "description"),

            static::makeDatetimeField(__("fields.created_at"), "created_at")->filterable()->sortable(),
            static::makeDatetimeField(__("fields.updated_at"), "updated_at")->filterable()->sortable(),
        ];
    }

//    public function authorizedTo(Request $request, $ability): false
//    {
//        return false;
//    }

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
