<?php

namespace App\Nova;

use App\Models\Finance\Finance as Model;
use App\Nova\Actions\ActionHelper;
use App\Nova\Actions\DeleteFinance;
use App\Nova\Actions\RequestFinance;
use App\Nova\Actions\ResolveFinance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
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
            ID::make(),

            BelongsTo::make('Customer', 'customer', Customer::class)
                ->canSee(fn(Request $request) => $request->user()?->is_admin),

            Badge::make('Type', "type")->map([
                'withdraw' => 'danger',
                'deposit' => 'success',
            ]),

            Currency::make('Amount', function ($amount) {
                return abs($amount->amount);
            }),

            Text::make('Comment', 'comment'),

            static::makeDatetimeField(__("fields.created_at"), "created_at")
                ->sortable(),
            static::makeDatetimeField(__("fields.updated_at"), "updated_at")
                ->onlyOnDetail(),
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
        return [];
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
