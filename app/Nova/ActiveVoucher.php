<?php

namespace App\Nova;

use App\Nova\Actions\FreezeVoucher;
use App\Nova\Actions\GenerateVoucher;
use App\Nova\Actions\RedeemVoucher;
use App\Nova\Fields\DateTime;
use App\Nova\Filters\AmountFilter;
use Illuminate\Http\Request;
use Laravel\Nova\Exceptions\HelperNotSupported;
use App\Nova\Fields\Badge;
use App\Nova\Fields\BelongsTo;
use App\Nova\Fields\Currency;
use App\Nova\Fields\Text;
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
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!$user?->canSeeVouchersList()) return $query->whereRaw('1 = 0');

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

            DateTime::createdAt()->sortable()->filterable(),
            DateTime::updatedAt()
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
        return [
            AmountFilter::make()->withFilterType('min'),
            AmountFilter::make()->withFilterType('max'),
        ];
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
            RedeemVoucher::make()
                ->canSee(fn(Request $request) => $request->user()?->can("customer:voucher:redeem")),

            GenerateVoucher::make()
                ->canSee(fn(Request $request) => $request->user()?->can("customer:voucher:generate"))
                ->confirmButtonText(__("actions.generate"))
                ->cancelButtonText(__("actions.cancel")),

            FreezeVoucher::make($this)
                ->canSee(fn(Request $request) => $request->user()?->can("customer:voucher:freeze"))
                ->canRun(fn(Request $request) => $request->user()?->can("customer:voucher:freeze"))
        ];
    }

    public function getKey(): string
    {
        return "active-vouchers";
    }
}
