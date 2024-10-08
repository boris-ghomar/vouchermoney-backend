<?php

namespace App\Nova\Resources\Finance;

use App\Models\Finance\AbstractFinance;
use App\Models\Finance\Finance as Model;
use App\Models\Permission;
use App\Nova\Actions\ActionHelper;
use App\Nova\Actions\DeleteFinance;
use App\Nova\Actions\RequestFinance;
use App\Nova\Actions\ResolveFinance;
use App\Nova\Customer;
use App\Nova\Fields\Badge;
use App\Nova\Fields\BelongsTo;
use App\Nova\Fields\Currency;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\ID;
use App\Nova\Fields\Text;
use App\Nova\Filters\AmountFilter;
use App\Nova\Resource;
use App\Nova\Resources\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin Model
 */
class Finance extends Resource
{
    const ICON = "currency-dollar";

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

    public static $polling = true;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['id', 'comment'];

    public static $globallySearchable = false;

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        static::hideWhenNotAuthorized($request, $query, Permission::FINANCES_VIEW, Permission::CUSTOMER_FINANCE);

        static::forCustomer($request, $query);

        return $query;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make(__("fields.id"), "id"),

            BelongsTo::make(__("fields.customer"), 'customer', Customer::class)
                ->onlyForAdmins([Permission::CUSTOMERS_VIEW]),

            Text::make(__("fields.requester"), fn() => $this->requester->is_admin ? "Administrator" : $this->requester->name)
                ->onlyForCustomerAdmin(),

            BelongsTo::make(__("fields.requester"), 'requester', User::class)
                ->onlyForAdmins([Permission::CUSTOMERS_VIEW]),

            Badge::make(__("fields.type"), "type")->map([
                AbstractFinance::TYPE_WITHDRAW => 'danger',
                AbstractFinance::TYPE_DEPOSIT => 'success',
            ]),

            Currency::make(__("fields.amount"), "amount")->displayAsPositive(),

            Text::make('Comment', 'comment'),

            DateTime::createdAt()->sortable(),
            DateTime::updatedAt(),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param NovaRequest $request
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
     * @param NovaRequest $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return ActionHelper::make([
            RequestFinance::make(),
            DeleteFinance::make()->canSee(fn(Request $request) => $request->user()?->is_customer),
            ResolveFinance::make()
        ]);
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }

    public function authorizedToReplicate(Request $request): bool
    {
        return false;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
