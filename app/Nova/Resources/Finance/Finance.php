<?php

namespace App\Nova\Resources\Finance;

use App\Models\Finance\AbstractFinance as Model;
use App\Models\Permission;
use App\Nova\Actions\ActionHelper;
use App\Nova\Customer;
use App\Nova\Fields\Badge;
use App\Nova\Fields\BelongsTo;
use App\Nova\Fields\Currency;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\ID;
use App\Nova\Fields\Text;
use App\Nova\Filters\AmountFilter;
use App\Nova\Resource;
use App\Nova\Resources\Transaction\ActiveTransactions;
use App\Nova\Resources\Transaction\ArchivedTransaction;
use App\Nova\Resources\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Exceptions\HelperNotSupported;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\DependencyContainer\DependencyContainer;

/**
 * @mixin Model
 */
abstract class Finance extends Resource
{
    const ICON = "currency-dollar";

    public static string $model = Model::class;
    public static $title = 'id';
    public static $search = ['id'];

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        parent::indexQuery($request, $query);

        static::hideWhenNotAuthorized($request, $query, [Permission::FINANCES_VIEW, Permission::CUSTOMER_FINANCE]);

        static::forCustomer($request, $query);

        return $query;
    }

    /**
     * @throws HelperNotSupported
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make(__("fields.id"), "id"),

            BelongsTo::make(__("fields.customer"), 'customer', Customer::class)
                ->onlyForAdmins([Permission::CUSTOMERS_VIEW]),

            DependencyContainer::make([
                BelongsTo::make(__("fields.requester"), "requester", User::class)
            ])->onlyOnDetail()->dependsOnNot("requester_id", $request->user()?->id)
                ->canSee(fn() => $request->user()?->is_customer_admin && $request->user()?->customer_id === $this->customer_id),

            DependencyContainer::make([
                Text::make(__("fields.requester"), fn() => "You")
            ])->onlyOnDetail()->dependsOn("requester_id", $request->user()?->id),

            BelongsTo::make(__("fields.requester"), "requester", User::class)
                ->onlyForAdmins(),

            Badge::make(__("fields.type"), "type")->depositOrWithdraw(),

            Currency::make(__("fields.amount"), "amount")->displayAsPositive(),

            Text::make('Requester Comment', 'requester_comment')->onlyOnDetail(),

            Text::link("Transaction", function () {
                if (empty($this->transaction)) return null;

                $resource = $this->transaction instanceof \App\Models\Transaction\ArchivedTransaction ? ArchivedTransaction::uriKey() : ActiveTransactions::uriKey();

                return "/resources/$resource/{$this->transaction->id}";
            }, fn () => $this->transaction?->id),

            DateTime::createdAt()->sortable(),
            DateTime::updatedAt()->onlyForAdmins(),
        ];
    }

    public function filters(NovaRequest $request): array
    {
        return ActionHelper::make([
            AmountFilter::make()
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
