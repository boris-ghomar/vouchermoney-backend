<?php

namespace App\Nova\Resources\Finance;

use App\Models\Finance\AbstractFinance;
use App\Models\Finance\ArchivedFinance as Model;
use App\Nova\Actions\ActionHelper;
use App\Nova\Customer;
use App\Nova\Fields\Badge;
use App\Nova\Fields\BelongsTo;
use App\Nova\Fields\Currency;
use App\Nova\Fields\DateTime;
use App\Nova\Fields\FieldHelper;
use App\Nova\Fields\ID;
use App\Nova\Fields\Text;
use App\Nova\Filters\AmountFilter;
use App\Nova\Resource;
use App\Nova\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Http\Requests\NovaRequest;

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
        $user = $request->user();

        if ($user?->is_customer)
            $query->where("customer_id", $user->customer_id);

        return $query;
    }

    public static $polling = true;

    /**
     * Get the fields displayed by the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return FieldHelper::make([
            ID::make(__("fields.id"), "id")->sortable(),

            BelongsTo::make(__("fields.customer"), 'customer', Customer::class)
                ->onlyForAdmins(),

            BelongsTo::make(__("fields.requested_by"), 'user', User::class),

            Badge::make(__("fields.type"), "type")->map([
                AbstractFinance::TYPE_WITHDRAW => 'danger',
                AbstractFinance::TYPE_DEPOSIT => 'success',
            ]),

            Currency::make(__("fields.amount"), "amount")->displayAsPositive(),

            Text::make(__("fields.request_comment"), 'request_comment')->onlyOnDetail(),

            Badge::make(__("fields.status"), "status")
                ->asBoolean()->withIcons()->sortable()->filterable()
                ->labels(['Rejected', 'Approved']),

            Text::make('Resolver comment', 'resolved_comment')->onlyOnDetail(),

            DateTime::make(__("fields.resolved_at"), "resolved_at")->sortable()->filterable(),

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
}
