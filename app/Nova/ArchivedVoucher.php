<?php

namespace App\Nova;

use App\Nova\Fields\FieldHelper;
use Illuminate\Http\Request;
use Laravel\Nova\Exceptions\HelperNotSupported;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Voucher\ArchivedVoucher as Model;
use Illuminate\Database\Eloquent\Builder;

class ArchivedVoucher extends Resource
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
    public static $title = 'code';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'code',
    ];

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
//        if ($request->user()?->is_customer)
//            $query->where("customer_data->", $request->user()->customer_id);

        return $query;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     * @throws HelperNotSupported
     */
    public function fields(NovaRequest $request): array
    {
        return FieldHelper::make([
            Text::make(__("fields.code"), "code")->copyable()->filterable(),

            Currency::make(__("fields.amount"), "amount")
                ->sortable()->filterable(),

            Badge::make(__("fields.resolved_status"), "state")->map([
                Model::STATE_REDEEMED => "success",
                Model::STATE_EXPIRED => "danger"
            ])->labels([
                Model::STATE_REDEEMED => __("fields.redeemed"),
                Model::STATE_EXPIRED => __("fields.expired"),
            ])->filterable()->sortable(),

            Code::make(__("fields.customer"), "customer_data")->json()->onlyOnDetail(),
            Code::make(__("fields.recipient"), "recipient_data")->json()->onlyOnDetail(),

            static::makeDatetimeField(__("fields.resolved_at"), "resolved_at"),

            static::timestamps()
        ]);
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
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
