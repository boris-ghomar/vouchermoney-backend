<?php

namespace App\Nova\Resources\Voucher;

use App\Models\Permission;
use App\Models\User;
use App\Models\Voucher\Voucher as Model;
use App\Nova\Actions\ActionHelper;
use App\Nova\Actions\FreezeVoucher;
use App\Nova\Actions\GenerateVoucher;
use App\Nova\Actions\RedeemVoucher;
use App\Nova\Customer;
use App\Nova\Fields\Badge;
use App\Nova\Fields\BelongsTo;
use App\Nova\Fields\Currency;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\ID;
use App\Nova\Fields\MorphTo;
use App\Nova\Fields\Text;
use App\Nova\Filters\AmountFilter;
use App\Nova\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Exceptions\HelperNotSupported;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\User as UserModel;

/**
 * @mixin Model
 */
class ActiveVoucher extends Resource
{
    const ICON = "cash";

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
    public static $search = ['id', 'code'];

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        $query->when(empty($request->get('orderBy')), function(Builder $q) {
            $q->getQuery()->orders = [];

            return $q->orderByDesc("created_at");
        });

        static::hideWhenNotAuthorized($request, $query, Permission::VOUCHERS_VIEW, Permission::CUSTOMER_VOUCHER_VIEW);

        static::forCustomer($request, $query);

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
            ID::make(__("fields.id"), "id")->onlyForAdmins(),

            Text::make(__("fields.code"), "code")->copyable()->filterable(),

            BelongsTo::make(__("fields.customer"), "customer", Customer::class)
                ->onlyForAdmins([Permission::CUSTOMERS_VIEW]),

            Text::make(__("fields.creator"), function () {
                if (empty($this->creator)) return null;

                return "<a href='/resources/accounts/{$this->creator->id}'>{$this->creator->name}</a>";
            })->asHtml(),

            MorphTo::make(__("fields.creator"), "creator")
                ->onlyForAdmins([Permission::CUSTOMERS_VIEW]),

            Currency::make(__("fields.amount"), "amount")->sortable(),

            Badge::make(__("fields.status"), "active")
                ->map(["info", "success"])->labels([
                    __("fields.frozen"),
                    __("fields.active")
                ])->filterable()->sortable(),

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

    /**
     * Get the actions available for the resource.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [
            RedeemVoucher::make(),

            GenerateVoucher::make(),

            FreezeVoucher::make($this)
        ];
    }

    public function getKey(): string
    {
        return "active-vouchers";
    }
}
