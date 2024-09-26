<?php

namespace App\Nova\Lenses;

use App\Models\User;
use App\Nova\Voucher;
use Laravel\Nova\Exceptions\HelperNotSupported;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Lenses\Lens;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Voucher as Model;

class ResolvedVouchers extends Lens
{
    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [];

    public $name = "Resolved vouchers";

    /**
     * Get the query builder / paginator for the lens.
     *
     * @param  LensRequest  $request
     * @param  Builder  $query
     * @return Builder
     */
    public static function query(LensRequest $request, $query): Builder
    {
        $user = $request->user();

        if ($user && $user->is_customer && $user->customer_id) {
            $query->where("created_by", $user->customer_id)
                ->where("status", "<>", Model::STATUS_ACTIVE);
        }

        return $request->withOrdering($request->withFilters(
            $query
        ));
    }

    /**
     * Get the fields available to the lens.
     *
     * @param NovaRequest $request
     * @return array
     * @throws HelperNotSupported
     */
    public function fields(NovaRequest $request): array
    {
        return Voucher::getVoucherDefaultFields();
    }

    /**
     * Get the cards available on the lens.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the lens.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available on the lens.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the URI key for the lens.
     *
     * @return string
     */
    public function uriKey(): string
    {
        return 'resolved-vouchers';
    }
}
