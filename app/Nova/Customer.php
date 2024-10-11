<?php

namespace App\Nova;

use App\Models\Customer as Model;
use App\Models\Permission as PermissionModel;
use App\Nova\Actions\CreateCustomer;
use App\Nova\Fields\Badge;
use App\Nova\Fields\Currency;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\FieldHelper;
use App\Nova\Fields\HasMany;
use App\Nova\Fields\ID;
use App\Nova\Fields\Select;
use App\Nova\Fields\Text;
use App\Nova\Metrics\CustomerAvailableBalance;
use App\Nova\Resources\Finance\ActiveFinance;
use App\Nova\Resources\Finance\ArchivedFinance;
use App\Nova\Resources\Finance\Finance;
use App\Nova\Resources\Transaction\ActiveTransactions;
use App\Nova\Resources\Transaction\ArchivedTransaction;
use App\Nova\Resources\Transaction\Transaction;
use App\Nova\Resources\User\Account;
use App\Nova\Resources\Voucher\ActiveVoucher;
use App\Nova\Resources\Voucher\ArchivedVoucher;
use Illuminate\Http\Request;
use Laravel\Nova\Exceptions\HelperNotSupported;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin Model
 */
class Customer extends Resource
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
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['name'];



    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return FieldHelper::make([
            ID::make(__("fields.id"), "id")->sortable()
                ->onlyForAdmins(),

            Text::make(__("fields.name"), "name")
                ->sortable()->exceptOnForms(),

            Text::make(__("fields.name"), "name")->onlyOnForms()
                ->hideWhenCreating()->rules("string", "max:180")->onlyForCustomersAdmin(),

            Text::make(__("fields.name"), "name")->onlyOnForms()
                ->hideWhenCreating()->rules("string", "max:180")->onlyForSuper(),

            Currency::make(__("fields.balance"), "balance")
                ->onlyForAdmins()->sortable()->filterable()->exceptOnForms(),

            Select::make(__("fields.type"), "type")->onlyOnForms()
                ->options([
                    Model::TYPE_RESELLER => __("fields.reseller"),
                    Model::TYPE_MERCHANT => __("fields.merchant")
                ])->onlyForSuper(),

            Badge::make(__("fields.type"), "type")->map([
                Model::TYPE_RESELLER => "info",
                Model::TYPE_MERCHANT => "success"
            ])->filterable()->onlyForAdmins(),

            DateTime::createdAt()->onlyForCustomersAdmin()->exceptOnForms(),
            DateTime::createdAt()->onlyForAdmins()->exceptOnForms(),
            DateTime::updatedAt()->onlyForAdmins()->exceptOnForms(),

            HasMany::make(__("fields.users"), "users", Account::class)->onlyOnDetail()
                ->collapsable()->collapsedByDefault()->onlyForAdmins([PermissionModel::CUSTOMERS_VIEW]),

            HasMany::make("Vouchers", "vouchers", ActiveVoucher::class)
                ->onlyForAdmins([PermissionModel::VOUCHERS_VIEW])->collapsedByDefault()->onlyOnDetail(),

            HasMany::make("Archived vouchers", "archivedVouchers", ArchivedVoucher::class)
                ->onlyForAdmins([PermissionModel::VOUCHERS_VIEW])->collapsedByDefault()->onlyOnDetail(),

            HasMany::make("Transactions", "transactions", ActiveTransactions::class)
                ->onlyForAdmins([PermissionModel::TRANSACTIONS_VIEW])->collapsedByDefault()->onlyOnDetail(),

            HasMany::make("Archived transactions", "archivedTransactions", ArchivedTransaction::class)
                ->onlyForAdmins([PermissionModel::TRANSACTIONS_VIEW])->collapsedByDefault()->onlyOnDetail(),

            HasMany::make("Finances", "finances", ActiveFinance::class)
                ->onlyForAdmins([PermissionModel::FINANCES_MANAGEMENT, PermissionModel::FINANCES_VIEW])->collapsedByDefault()->onlyOnDetail(),

            HasMany::make("Archived finances", "archivedFinances", ArchivedFinance::class)
                ->onlyForAdmins([PermissionModel::FINANCES_VIEW])->collapsedByDefault()->onlyOnDetail(),
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

    /**
     * Get the cards available for the request.
     *
     * @param NovaRequest $request
     * @return array
     * @throws HelperNotSupported
     */
    public function cards(NovaRequest $request): array
    {
        return [
            CustomerAvailableBalance::make()->onlyOnDetail()
                ->canSee(fn (Request $request) => $request->user()?->can(PermissionModel::CUSTOMERS_VIEW)),
        ];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function actions(NovaRequest $request): array
    {
        return [
            CreateCustomer::make()
        ];
    }
}
