<?php

namespace App\Nova\Resources\Transaction;

use App\Models\Permission;
use App\Models\Transaction\Transaction as Model;
use App\Models\User;
use App\Nova\Actions\ActionHelper;
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
use Laravel\Nova\Http\Requests\NovaRequest;

class Transaction extends Resource
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
        static::hideWhenNotAuthorized($request, $query, Permission::TRANSACTIONS_VIEW, Permission::CUSTOMER_TRANSACTIONS_VIEW);

        static::forCustomer($request, $query);

        return $query;
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make(__("fields.id"), "id")->sortable(),

            BelongsTo::make(__("fields.customer"), "customer")
                ->onlyForAdmins(),

            Badge::make(__("fields.type"), "type")->map([
                    "withdraw" => "danger",
                    "deposit" => "success"
                ])->labels([
                    "withdraw" => __("fields.withdraw"),
                    "deposit" => __("fields.deposit")
                ]),

            Currency::make(__("fields.amount"), "amount")
                ->displayAsPositive()->sortable()->filterable(),

            Text::make(__("fields.description"), "description"),

            MorphTo::make("Attachment", "model"),

            DateTime::createdAt()->filterable()->sortable(),
            DateTime::updatedAt(),
        ];
    }

    public static function authorizedToCreate(Request $request): false
    {
        return false;
    }

    public function authorizedToDelete(Request $request): false
    {
        return false;
    }

    public function authorizedToForceDelete(Request $request): false
    {
        return false;
    }

    public function authorizedToReplicate(Request $request): false
    {
        return false;
    }

    public function authorizedToUpdate(Request $request): false
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
