<?php

namespace App\Nova\Resources\Voucher;

use App\Models\Permission;
use App\Models\Voucher\AbstractVoucher as Model;
use App\Nova\Actions\ActionHelper;
use App\Nova\Customer;
use App\Nova\CustomerApiToken;
use App\Nova\Fields\BelongsTo;
use App\Nova\Fields\Currency;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\FieldHelper;
use App\Nova\Fields\ID;
use App\Nova\Fields\MorphTo;
use App\Nova\Fields\Text;
use App\Nova\Filters\AmountFilter;
use App\Nova\Resource;
use App\Nova\Resources\Finance\ActiveFinance;
use App\Nova\Resources\Finance\ArchivedFinance;
use App\Nova\Resources\Transaction\ActiveTransactions;
use App\Nova\Resources\Transaction\ArchivedTransaction;
use App\Nova\Resources\User\Account;
use App\Nova\Resources\User\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Exceptions\HelperNotSupported;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\User as UserModel;
use App\Models\CustomerApiToken as CustomerApiTokenModel;
use Outl1ne\DependencyContainer\DependencyContainer;

/**
 * @mixin Model
 */
abstract class Voucher extends Resource
{
    const ICON = "cash";

    public static $title = 'code';
    public static $search = ['id', 'code'];
    protected static string $default_order_column = "created_at";

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        parent::indexQuery($request, $query);

        static::hideWhenNotAuthorized($request, $query, [Permission::VOUCHERS_VIEW, Permission::CUSTOMER_VOUCHER_VIEW]);

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
        /** @var UserModel $user */
        $user = $request->user();

        $fields = [
            ID::make(__("fields.id"), "id")->onlyForAdmins(),

            BelongsTo::make(__("fields.customer"), "customer", Customer::class)
                ->onlyForAdmins([Permission::CUSTOMERS_VIEW])->exceptOnForms(),

            Text::make(__("fields.code"), "code")->copyable()->filterable()->exceptOnForms(),

            Currency::make(__("fields.amount"), "amount")->sortable()->exceptOnForms(),

            MorphTo::make("Creator", "creator")->onlyForAdmins([Permission::CUSTOMERS_VIEW])
                ->types([
                    Account::class => UserModel::class,
                    CustomerApiToken::class => CustomerApiTokenModel::class
                ])->exceptOnForms(),

            Text::link("Transaction", function () use ($request) {
                if (empty($this->transaction) || $this->transaction->customer_id !== $request->user()?->customer_id) return null;

                $resource = $this->transaction instanceof \App\Models\Transaction\ArchivedTransaction ? ArchivedTransaction::uriKey() : ActiveTransactions::uriKey();

                return "/resources/$resource/{$this->transaction->id}";
            }, fn () => $this->transaction?->id)->exceptOnForms(),
        ];

        $fields[] = DateTime::createdAt()->onlyForAdmins()->sortable()->filterable();
        $fields[] = DateTime::updatedAt()->onlyForAdmins();

        return FieldHelper::make($fields);
    }

    public function authorizedToRunAction(NovaRequest $request, Action $action): bool
    {
        return $action->authorizedToRun($request, $this);
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

    public function filters(NovaRequest $request): array
    {
        return ActionHelper::make([AmountFilter::make()]);
    }
}
