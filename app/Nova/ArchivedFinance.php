<?php

namespace App\Nova;

use App\Models\Finance\AbstractFinance;
use App\Models\Finance\ArchivedFinance as Model;
use App\Nova\Fields\FieldHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin Model
 */
class ArchivedFinance extends Resource
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
    public static $search = [
        'id','customer_id','amount','status','resolved_by'
    ];

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        $user = $request->user();

        if ($user?->is_customer)
            $query->where("customer_id", $user->customer_id);

        return $query;
    }

    public static $polling = true;

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

            BelongsTo::make(__("fields.customer"), 'customer', Customer::class)
                ->canSee(fn(Request $request) => $request->user()?->is_admin),

            Badge::make(__("fields.type"), "type")->map([
                AbstractFinance::TYPE_WITHDRAW => 'danger',
                AbstractFinance::TYPE_DEPOSIT => 'success',
            ]),

            Currency::make(__("fields.amount"), function ($amount) {
                return abs($amount->amount);
            }),

            Text::make(__("fields.request_comment"), 'request_comment')->onlyOnDetail(),

            Badge::make(__("fields.status"), "status")
                ->map(['danger', 'success'])->withIcons()->sortable()->filterable()
                ->labels(['Approved', 'Rejected']),

            Text::make('Resolver comment', 'resolved_comment')->onlyOnDetail(),

            static::makeDatetimeField(__("fields.resolved_at"), "resolved_at")->sortable()->filterable(),

            static::timestamps()
        ]);
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
        return [];
    }

   public static function authorizedToCreate(Request $request): false
   {
       return false;
   }

   public function authorizedToUpdate(Request $request): false
   {
       return false;
   }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }
    public function authorizedToDelete(Request $request): false
    {
        return false;
    }
}
