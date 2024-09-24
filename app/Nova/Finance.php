<?php

namespace App\Nova;

use App\Models\Finance as Model;
use App\Nova\Actions\CreateFinance;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Models\Customer as CustomerModel;
use Illuminate\Database\Eloquent\Builder;

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
    public static $search = [];

    public static function indexQuery(NovaRequest $request, $query): Builder
    {
        $user = auth()->user();

        if ($user?->is_customer)
            return $query->where('customer_id', $user->customer_id);

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
            ID::make()->sortable(),

            BelongsTo::make('Customer', 'customer', Customer::class)
                ->canSee(fn(Request $request) => $request->user()?->is_admin),

            Currency::make('Amount', 'amount'),

            Textarea::make('Request comment', 'request_comment')->onlyOnDetail(),

            Textarea::make('Approved comment', 'approved_comment')->onlyOnDetail(),

            Badge::make('Status', function () {
                return $this->status;
            })->map([
                'approved' => 'success',
                'rejected' => 'danger',
                'pending' => 'info',
                'canceled' => 'warning',
            ])->withIcons(),

            BelongsTo::make("Resolved by", "resolver", User::class)
                ->canSee(fn(Request $request) => $request->user()?->is_admin),
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

    /**
     * Get the actions available for the resource.
     *
     * @param NovaRequest $request
     * @return array
     */

    public function actions(NovaRequest $request): array
    {
        return [
            CreateFinance::make()->setType('deposit'),
            CreateFinance::make()->setType('withdraw'),
        ];
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public function authorizedToUpdate(Request $request): bool
    {
        return false;
    }
}
