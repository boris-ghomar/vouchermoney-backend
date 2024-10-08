<?php

namespace App\Nova\Resources\Voucher;

use App\Models\Permission;
use App\Models\User;
use App\Models\Voucher\ArchivedVoucher as Model;
use App\Nova\Actions\ActionHelper;
use App\Nova\Fields\Badge;
use App\Nova\Fields\Code;
use App\Nova\Fields\Currency;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\FieldHelper;
use App\Nova\Fields\HasMany;
use App\Nova\Fields\ID;
use App\Nova\Fields\Text;
use App\Nova\Filters\AmountFilter;
use App\Nova\Resource;
use App\Nova\Resources\Transaction\ArchivedTransaction;
use App\Nova\Resources\Transaction\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Exceptions\HelperNotSupported;
use App\Nova\Fields\MorphOne;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\DependencyContainer\DependencyContainer;

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
    public static $search = ['id', 'code'];

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        static::hideWhenNotAuthorized($request, $query, Permission::VOUCHERS_VIEW, Permission::CUSTOMER_VOUCHER_VIEW);

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
            ID::make(__("fields.id"), "id")->onlyForAdmins(),

            Text::make(__("fields.code"), "code")->copyable()->filterable(),

            Currency::make(__("fields.amount"), "amount")
                ->sortable(),

            Badge::make(__("fields.resolved_status"), "state")->filterable()->sortable()
                ->map([Model::STATE_REDEEMED => "success", Model::STATE_EXPIRED => "danger"]),

            Text::make(__("fields.note"), "recipient_note"),

            Code::make(__("fields.creator"), "creator_data")->json()->onlyOnDetail()->onlyForAdmins(),
            Code::make(__("fields.customer"), "customer_data")->json()->onlyOnDetail()->onlyForAdmins(),
            Code::make(__("fields.recipient"), "recipient_data")->json()->onlyOnDetail()->onlyForAdmins(),

            DateTime::make(__("fields.resolved_at"), "resolved_at"),
            DateTime::createdAt()->onlyOnDetail()->canSee(function (Request $request) {
                /** @var User $user */
                $user = $request->user();

                return ! empty($this->customer_data["id"]) && $user && $user->customer_id === $this->customer_data["id"];
            }),
            DateTime::createdAt()->sortable()->filterable()->onlyForAdmins(),
            DateTime::updatedAt()->onlyForAdmins(),

            DependencyContainer::make([
                MorphOne::make("Transaction", "transaction", Transaction::class)->onlyForAdmins(),
            ])->dependsOnNotEmpty("transaction")->onlyOnDetail(),

            DependencyContainer::make([
                MorphOne::make("Transaction", "archived_transaction", ArchivedTransaction::class)->onlyForAdmins(),
            ])->dependsOnNullOrZero("transaction")->onlyOnDetail(),

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
