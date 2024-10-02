<?php

namespace App\Nova;

use App\Nova\Actions\ActionHelper;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\FieldHelper;
use App\Nova\Fields\HasMany;
use App\Nova\Filters\AmountFilter;
use Illuminate\Http\Request;
use Laravel\Nova\Exceptions\HelperNotSupported;
use App\Nova\Fields\Badge;
use App\Nova\Fields\Code;
use App\Nova\Fields\Currency;
use App\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Voucher\ArchivedVoucher as Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Model
 */
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
        if ($request->user()?->is_customer)
            $query->where("customer_data->", $request->user()->customer_id);

        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!$user?->canSeeVouchersList()) return $query->whereRaw('1 = 0');

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

            Badge::make(__("fields.resolved_status"), "state")->asBoolean()
                ->labels([__("fields.expired"), __("fields.redeemed")])->filterable()->sortable(),

            Code::make(__("fields.customer"), "customer_data")->json()->onlyOnDetail()
                ->onlyForAdmins(),
            Code::make(__("fields.recipient"), "recipient_data")->json()->onlyOnDetail()
                ->onlyForAdmins(),

            DateTime::make(__("fields.resolved_at"), "resolved_at"),
            DateTime::createdAt()->sortable()->filterable(),
            DateTime::updatedAt()->onlyForAdmins(),

            HasMany::make("Activities", "activities", VoucherActivity::class)
                ->onlyForAdmins()->readonly()
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
     * Get the filters available for the resource.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request): array
    {
        return ActionHelper::make([
            AmountFilter::make()
        ]);
    }
}
