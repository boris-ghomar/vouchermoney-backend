<?php

namespace App\Nova\Resources\Finance;

use App\Models\Finance\AbstractFinance;
use App\Models\Finance\ArchivedFinance as Model;
use App\Models\Permission;
use App\Models\User;
use App\Nova\Actions\ActionHelper;
use App\Nova\Fields\Badge;
use App\Nova\Fields\Code;
use App\Nova\Fields\Currency;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\FieldHelper;
use App\Nova\Fields\ID;
use App\Nova\Fields\MorphOne;
use App\Nova\Fields\Text;
use App\Nova\Filters\AmountFilter;
use App\Nova\Resource;
use App\Nova\Resources\Transaction\ArchivedTransaction;
use App\Nova\Resources\Transaction\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Exceptions\HelperNotSupported;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\DependencyContainer\DependencyContainer;

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
    public static $search = ['id'];

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        static::hideWhenNotAuthorized($request, $query, Permission::FINANCES_VIEW, Permission::CUSTOMER_FINANCE);

        static::forCustomer($request, $query, "customer_data->id");

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
            ID::make(__("fields.id"), "id")->sortable(),

            Text::make("Customer", function () {
                return ! empty($this->customer_data['id']) ? "<a style='color: #30B1EF; font-weight: bold' href='/resources/customers/{$this->customer_data['id']}'>{$this->customer_data['name']}</a>" : null;
            })->asHtml()->onlyForAdmins([Permission::CUSTOMERS_VIEW]),

            Text::make("Requester", function () {
                if (empty($this->requester_data['id'])) return null;

                $name = $this->requester_data['name'] . ($this->requester_data['customer_id'] !== null ? " [{$this->customer_data['name']}]" : "");

                return $name === "Administrator" ? $name : "<a style='color: #30B1EF; font-weight: bold' href='/resources/users/{$this->requester_data['id']}'>$name</a>";
            })->asHtml()->onlyForAdmins([Permission::CUSTOMERS_VIEW]),

            Text::make("Resolver", function () {
                if (empty($this->resolver_data['id'])) return null;

                $name = $this->resolver_data['name'];

                return $name === "Administrator" ? $name : "<a style='color: #30B1EF; font-weight: bold' href='/resources/admins/{$this->resolver_data['id']}'>$name</a>";
            })->asHtml()->onlyForAdmins(),

            Badge::make(__("fields.type"), "type")->map([
                AbstractFinance::TYPE_WITHDRAW => 'danger',
                AbstractFinance::TYPE_DEPOSIT => 'success',
            ]),

            Currency::make(__("fields.amount"), "amount")->displayAsPositive(),

            Text::make(__("fields.requester_comment"), 'requester_comment')->onlyOnDetail(),

            Badge::make(__("fields.status"), "status")
                ->asBoolean()->withIcons()->sortable()->filterable()
                ->labels(['Rejected', 'Approved']),

            Text::make('Resolver comment', 'resolver_comment')->onlyOnDetail(),

            DateTime::make(__("fields.resolved_at"), "resolved_at")->sortable()->filterable(),

            Code::make("Customer Data", "customer_data")->json()->onlyOnDetail()->onlyForAdmins(),
            Code::make("Requester Data", "requester_data")->json()->onlyOnDetail()->onlyForAdmins(),
            Code::make("Resolver Data", "resolver_data")->json()->onlyOnDetail()->onlyForAdmins(),

            DependencyContainer::make([
                MorphOne::make("Transaction", "transaction", Transaction::class),
            ])->dependsOnNotEmpty("transaction")->canSee(fn(Request $request) => $request->user()?->is_admin),

            DependencyContainer::make([
                MorphOne::make("Transaction", "archived_transaction", ArchivedTransaction::class),
            ])->dependsOnNullOrZero("transaction")->canSee(fn(Request $request) => $request->user()?->is_admin),

            DateTime::createdAt(),
            DateTime::updatedAt()->onlyForAdmins()
        ]);
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

    public static function label(): string
    {
        return auth()->user()?->is_customer ? "Finance History" : "Archived Finances";
    }
}
