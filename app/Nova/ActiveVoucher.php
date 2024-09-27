<?php

namespace App\Nova;

use App\Nova\Actions\FreezeVoucher;
use App\Nova\Actions\GenerateVoucher;
use Illuminate\Http\Request;
use Laravel\Nova\Exceptions\HelperNotSupported;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Voucher\Voucher as Model;
use Illuminate\Database\Eloquent\Builder;

class ActiveVoucher extends Resource
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
        $user = $request->user();

        $query->when(empty($request->get('orderBy')), function(Builder $q) {
            $q->getQuery()->orders = [];

            return $q->orderByDesc("created_at");
        });

        if ($user?->is_customer) {
            $query->where("customer_id", $user->customer_id);
        }

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
        return [
            Text::make(__("fields.code"), "code")->copyable()->filterable(),

            BelongsTo::make(__("fields.customer"), "customer", Customer::class)
                ->canSee(fn(Request $request) => $request->user()?->is_admin),

            Currency::make(__("fields.amount"), "amount")
                ->sortable()->filterable(),

            Badge::make(__("fields.status"), "active")
                ->map(["info", "success"])->labels([
                    __("fields.frozen"),
                    __("fields.active")
                ])
                ->filterable()->sortable(),

            static::makeDatetimeField(__("fields.created_at"), "created_at")
                ->sortable()->filterable(),
            static::makeDatetimeField(__("fields.updated_at"), "updated_at")
                ->onlyOnDetail(),
        ];
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
        return [
            GenerateVoucher::make()
                ->canSee(fn(Request $request) => $request->user()?->can("customer:voucher:generate"))
                ->confirmButtonText(__("actions.generate"))
                ->cancelButtonText(__("actions.cancel")),

            FreezeVoucher::make()
                ->canSee(fn(Request $request) => $request->user()?->can("customer:voucher:freeze"))
                ->canRun(fn(Request $request) => $request->user()?->can("customer:voucher:freeze"))
                ->confirmButtonText(__("actions.freeze"))
                ->cancelButtonText(__("actions.cancel"))
                ->confirmText(__("actions.freeze_description"))
        ];
    }

    public function getKey(): string
    {
        return "active-vouchers";
    }
}
