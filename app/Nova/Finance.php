<?php

namespace App\Nova;

use App\Models\Finance\Finance as Model;
use App\Nova\Actions\ActionHelper;
use App\Nova\Actions\DeleteFinance;
use App\Nova\Actions\RequestFinance;
use App\Nova\Actions\ResolveFinance;
use App\Nova\Fields\DateTime;
use App\Nova\Filters\AmountFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Nova\Fields\Badge;
use App\Nova\Fields\BelongsTo;
use App\Nova\Fields\Currency;
use App\Nova\Fields\ID;
use App\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @mixin Model
 */
class Finance extends Resource
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
    public static $search = ['id', 'comment'];

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        $user = $request->user();

        if ($user?->is_customer)
            $query->where("customer_id", $user->customer_id);

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
                ->onlyForAdmins(),

            BelongsTo::make(__("fields.requested_by"), 'user', User::class),

            Badge::make(__("fields.type"), "type")->map([
                'withdraw' => 'danger',
                'deposit' => 'success',
            ]),

            Currency::make(__("fields.amount"), "amount")->displayAsPositive(),

            Text::make('Comment', 'comment'),

            DateTime::createdAt()->sortable(),
            DateTime::updatedAt(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function filters(NovaRequest $request): array
    {
        return [
            AmountFilter::make()->withFilterType('min'),
            AmountFilter::make()->withFilterType('max'),
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    public static $polling = true;

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
